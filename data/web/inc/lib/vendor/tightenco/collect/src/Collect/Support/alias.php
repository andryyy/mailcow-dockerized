<?php

$aliases = [
    Tightenco\Collect\Contracts\Support\Arrayable::class => Illuminate\Contracts\Support\Arrayable::class,
    Tightenco\Collect\Contracts\Support\Jsonable::class => Illuminate\Contracts\Support\Jsonable::class,
    Tightenco\Collect\Contracts\Support\Htmlable::class => Illuminate\Contracts\Support\Htmlable::class,
    Tightenco\Collect\Support\Arr::class => Illuminate\Support\Arr::class,
    Tightenco\Collect\Support\Collection::class => Illuminate\Support\Collection::class,
    Tightenco\Collect\Support\Enumerable::class => Illuminate\Support\Enumerable::class,
    Tightenco\Collect\Support\HigherOrderCollectionProxy::class => Illuminate\Support\HigherOrderCollectionProxy::class,
    Tightenco\Collect\Support\HigherOrderWhenProxy::class => Illuminate\Support\HigherOrderWhenProxy::class,
    Tightenco\Collect\Support\LazyCollection::class => Illuminate\Support\LazyCollection::class,
    Tightenco\Collect\Support\Traits\EnumeratesValues::class => Illuminate\Support\Traits\EnumeratesValues::class,
];

foreach ($aliases as $tighten => $illuminate) {
    if (! class_exists($illuminate) && ! interface_exists($illuminate) && ! trait_exists($illuminate)) {
        class_alias($tighten, $illuminate);
    }
}
