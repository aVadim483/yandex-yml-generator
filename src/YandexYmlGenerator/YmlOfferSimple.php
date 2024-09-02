<?php

namespace avadim\YandexYmlGenerator;

use avadim\YandexYmlGenerator\YmlOfferAbstract;

class YmlOfferSimple extends YmlOffer
{
    protected string $offerType = 'simple';

    protected array $childNodes = [
        'name' => 'required|size:1,120',
        'url' => 'url|size_max:512',
    ];
}