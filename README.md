# Hashidable from Coder's Cantina

> An adapted bridge for using [laravel-hashids](https://github.com/vinkla/laravel-hashids) in Laravel models. 

## Features
- Hashid route model binding
- Individual salt per model
- Optional individual configuration per model
- Helper methods for encoding, decoding and finding by hashid
- Collection support for working with multiple hashids
- Performance optimizations with factory caching

## 🏗 Install

Install the package via composer using this command:

```bash
composer require coderscantina/hashidable
```

## ⚙️ Usage

Add the Hashidable trait to your model

```php
use CodersCantina\Hashidable;

class Foo extends Model
{
    use Hashidable;
}
```

### Route Model Binding

Expose the hashid in a resource

```php
class FooResource extends JsonResource
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
* @param  \App\Models\Foo  $foo
* @return \Illuminate\Http\Response
*/
public function show(Foo $foo)
{
    return new FooResource($foo);
}
```

### Working with Single Models/IDs

Static methods to work with hashIds:

```php
Foo::encodeHashId(1);             // Convert ID to hashid
Foo::decodeHashId('A3');          // Convert hashid to ID
Foo::findByHashId('A3');          // Find model by hashid
Foo::findByHashIdOrFail('A3');    // Find model by hashid or throw exception
```

### Working with Collections/Arrays

Methods for working with multiple models or IDs:

```php
// Encode multiple IDs
Foo::encodeHashIds([1, 2, 3]);    // Returns array of hashids

// Decode multiple hashids
Foo::decodeHashIds(['A3', 'B7']); // Returns array of IDs

// Find multiple models by hashids
Foo::findByHashIds(['A3', 'B7']); // Returns collection of models
```

### Custom Configuration

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
