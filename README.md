# Hashidable from Coder's Cantina

> An adapted bridge for using [laravel-hashids](https://github.com/vinkla/laravel-hashids) in laravel models. 

## 🏗 Install

Install the package via composer using this command:

```bash
composer require coderscantina/hashidable
```

## ⚙️ Usage

Add the Hashidable trait to your model

```php
use CodersCantina\Hashidable;

class Phone extends Model
{
    use Hashidable;
}

```

Expose the hashid in a resource

```php
class PhoneResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getRouteKey(),
        ];
    }
}

```

Resolve the model via hashid in a controller

```php
/**
* @param  \App\Models\Phone  $phone
* @return \Illuminate\Http\Response
*/
public function show(Phone $phone)
{
    return new PhoneResource($phone);
}
```

Static methods to work with hashIds:

```php
Foo::encodeHashId(1);
Foo::decodeHashId('A3');
Foo::findByHashId('A3');
```

Overwrite config with a model like `App\User::class`

```php
# config/hashids.php

'connections' => [

    'main' => [
        'salt' => env('HASHIDS_SALT'),
        'length' => 8,
        'alphabet' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    ],

    \App\User::class => [
        'salt' => env('HASHIDS_SALT'),
        'length' => 5,
        'alphabet' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    ],

],
```

See for more information [Route Model Binding](https://laravel.com/docs/master/routing#route-model-binding)
