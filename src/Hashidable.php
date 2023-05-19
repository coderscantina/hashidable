<?php

namespace CodersCantina\Hashids;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Arr;

trait Hashidable
{
    public static function getHashidsFactory(): \Hashids\Hashids
    {
        $connections = config('hashids.connections');
        $config = Arr::get($connections, __CLASS__, $connections[config('hashids.default')]);

        return app('hashids.factory')->make(array_replace(
            $config,
            [
                'salt' => __CLASS__ . $config['salt']
            ]
        ));
    }

    public function getRouteKey(): string
    {
        return self::encodeHashId(parent::getRouteKey());
    }

    public function resolveRouteBinding($hashId, $field = null)
    {
        return self::findByHashIdOrFail($hashId);
    }

    public static function findByHashId(?string $hashId = null): ?Model
    {
        if ($id = self::decodeHashId($hashId)) {
            $model = new self();

            return $model->where($model->getRouteKeyName(), $id)->first();
        }

        return null;
    }

    public static function findByHashIdOrFail(?string $hashId, $columns = ['*']): ?Model
    {
        $model = new self();

        return $model->findOrFail(self::decodeHashId($hashId), $columns);
    }

    public static function decodeHashId(?string $value): ?int
    {
        $result = self::getHashidsFactory()->decode($value);

        return count($result) ? $result[0] : null;
    }

    public static function encodeHashId(int $value): string
    {
        return self::getHashidsFactory()->encode($value);
    }
}
