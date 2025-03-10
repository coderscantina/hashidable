<?php

namespace CodersCantina\Hashids;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait Hashidable
{
    /**
     * Store hashids factory instances to prevent redundant instantiation
     *
     * @var array<string, \Hashids\Hashids>
     */
    protected static array $hashidsFactory = [];

    /**
     * Get the Hashids factory instance for this model
     *
     * @return \Hashids\Hashids
     */
    public static function getHashidsFactory(): \Hashids\Hashids
    {
        $class = static::class;

        if (!isset(static::$hashidsFactory[$class])) {
            $connections = config('hashids.connections');
            $config = Arr::get($connections, $class, $connections[config('hashids.default')]);

            static::$hashidsFactory[$class] = app('hashids.factory')->make(
                array_replace(
                    $config,
                    [
                        'salt' => $class . $config['salt'],
                    ]
                )
            );
        }

        return static::$hashidsFactory[$class];
    }

    /**
     * Get the route key for the model
     *
     * @return string
     */
    public function getRouteKey(): string
    {
        return static::encodeHashId((int)parent::getRouteKey());
    }

    /**
     * Resolve route binding for the model
     *
     * @param mixed $hashId
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($hashId, $field = null): ?Model
    {
        return static::findByHashIdOrFail($hashId);
    }

    /**
     * Find a model by its hashid
     *
     * @param string|null $hashId
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findByHashId(?string $hashId = null): ?Model
    {
        if ($id = static::decodeHashId($hashId)) {
            $model = new static();

            return $model->where($model->getRouteKeyName(), $id)->first();
        }

        return null;
    }

    /**
     * Find a model by its hashid or fail
     *
     * @param string|null $hashId
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByHashIdOrFail(?string $hashId, $columns = ['*']): Model
    {
        $id = static::decodeHashId($hashId);
        $model = new static();

        return $model->findOrFail($id, $columns);
    }

    /**
     * Decode a hashid into an integer ID
     *
     * @param string|null $value
     * @return int|null
     */
    public static function decodeHashId(?string $value): ?int
    {
        if (!$value) {
            return null;
        }
        $result = static::getHashidsFactory()->decode($value);

        return count($result) ? $result[0] : null;
    }

    /**
     * Encode an integer ID into a hashid
     *
     * @param int $value
     * @return string
     */
    public static function encodeHashId(int $value): string
    {
        return static::getHashidsFactory()->encode($value);
    }

    /**
     * Encode an array of integer IDs into hashids
     *
     * @param array<int> $ids
     * @return array<string>
     */
    public static function encodeHashIds(array $ids): array
    {
        return array_map(function (int $id): string {
            return static::encodeHashId($id);
        }, $ids);
    }

    /**
     * Decode an array of hashids into integer IDs
     *
     * @param array<string> $hashIds
     * @return array<int>
     */
    public static function decodeHashIds(array $hashIds): array
    {
        if (empty($hashIds)) {
            return [];
        }

        $result = [];
        foreach ($hashIds as $hashId) {
            if ($id = static::decodeHashId($hashId)) {
                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * Find multiple models by their hashids
     *
     * @param array<string> $hashIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByHashIds(array $hashIds): Collection
    {
        if (empty($hashIds)) {
            return Collection::make();
        }

        $ids = static::decodeHashIds($hashIds);

        if (empty($ids)) {
            return Collection::make();
        }

        return static::whereIn((new static)->getRouteKeyName(), $ids)->get();
    }
}
