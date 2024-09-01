<?php

namespace avadim\YandexYmlGenerator;

use DOMException;

/**
 * @method $this series($arg)
 * @method $this author($arg)
 * @method $this vendorCode($arg)
 * @method $this vendor($arg)
 * @method $this expiry($arg)
 * @method $this rec($arg)
 * @method $this typePrefix($arg)
 * @method $this countryOfOrigin($arg)
 * @method $this ISBN($arg)
 * @method $this volume($arg)
 * @method $this part($arg)
 * @method $this language($arg)
 * @method $this binding($arg)
 * @method $this tableOfContents($arg)
 * @method $this performedBy($arg)
 * @method $this performanceType($arg)
 * @method $this storage($arg)
 * @method $this format($arg)
 * @method $this recordingLength($arg)
 * @method $this artist($arg)
 * @method $this media($arg)
 * @method $this starring($arg)
 * @method $this director($arg)
 * @method $this originalName($arg)
 * @method $this country($arg)
 * @method $this worldRegion($arg)
 * @method $this region($arg)
 * @method $this dataTour($arg)
 * @method $this hotelStars($arg)
 * @method $this room($arg)
 * @method $this meal($arg)
 * @method $this priceMin($arg)
 * @method $this priceMax($arg)
 * @method $this options($arg)
 * @method $this hall($arg)
 * @method $this hallPart($arg)
 * @method $this isPremiere($arg)
 * @method $this isKids($arg)
 * @method $this vat($arg)
 * @method $this downloadable($arg)
 * @method $this adult($arg)
 * @method $this delivery($arg)
 * @method $this manufacturerWarranty($arg)
 * @method $this pageExtent($arg)
 *
 * @method $this origin($arg)
 * @method $this warranty($arg)
 * @method $this sale($arg)
 * @method $this sales_notes($arg)
 * @method $this pages($arg)
 * @method $this page_extent($arg)
 * @method $this contents($arg)
 * @method $this performer($arg)
 * @method $this performance($arg)
 * @method $this length($arg)
 * @method $this stars($arg)
 * @method $this premiere($arg)
 * @method $this kids($arg)
 * @method $this cpa($arg)
 *
 */
abstract class YmlOfferAbstract
{
    // parent document
    protected \DOMDocument $domDocument;

    protected \DOMElement $domElement;

    protected string $offerType;
    protected string $xmlEncoding;

    // обязательные узлы
    protected array $required = [];

    // допустимые узлы
    protected array $permitted = [];

    // правила валидации
    private array $rules = [];

    protected array $aliases
        = [
            'countryOfOrigin' => 'country_of_origin', 'tableOfContents' => 'table_of_contents',
            'performedBy' => 'performed_by', 'performanceType' => 'performance_type', 'recordingLength' => 'recording_length',
            'origin' => 'country_of_origin', 'warranty' => 'manufacturer_warranty', 'sale' => 'sales_notes',
            'isbn' => 'ISBN', 'pages' => 'page_extent', 'pageExtent' => 'page_extent', 'contents' => 'table_of_contents',
            'performer' => 'performed_by', 'performance' => 'performance_type', 'length' => 'recording_length',
            'hotelStars' => 'hotel_stars',
            'stars' => 'hotel_stars', 'priceMin' => 'price_min', 'priceMax' => 'price_max',
            'hallPart' => 'hall_part', 'premiere' => 'is_premiere', 'isPremiere' => 'is_premiere',
            'kids' => 'is_kids', 'isKids' => 'is_kids', 'groupId' => 'group_id', 'manufacturerWarranty' => 'manufacturer_warranty',
        ];


    public function __construct(\DOMDocument $document, string $offerType)
    {
        $this->domDocument = $document;
        $this->xmlEncoding = $document->xmlEncoding;
        $this->domElement = $document->createElement('offer');
        $this->offerType = $offerType;

        foreach ($this->required as $rule) {
            if (strpos($rule, '|')) {
                $parts = explode('|', $rule);
                $nodeName = array_shift($parts);
                $this->rules['required'][$nodeName] = $parts;
            }
            else {
                $this->rules['required'][$rule] = [];
            }
        }

        foreach ($this->permitted as $rule) {
            if (strpos($rule, '|')) {
                $parts = explode('|', $rule);
                $nodeName = array_shift($parts);
                $this->rules['permitted'][$nodeName] = $parts;
            }
            else {
                $this->rules['permitted'][$rule] = [];
            }
        }

        $p = [
            'simple' => [
                'group_id', 'minq', 'stepq', 'model', 'age', 'vendor', 'vendorCode', 'manufacturer_warranty',
                'downloadable', 'adult', 'rec',
            ],
            'arbitrary' => [
                'group_id', 'minq', 'stepq', 'age', 'vendorCode', 'manufacturer_warranty', 'adult', 'downloadable',
                'typePrefix', 'rec',
            ],
            'book' => [
                'age', 'manufacturer_warranty', 'downloadable', 'author', 'series', 'year', 'ISBN', 'volume', 'part',
                'language', 'binding', 'page_extent', 'minq', 'stepq', 'adult', 'table_of_contents',
            ],
            'audiobook' => [
                'adult', 'manufacturer_warranty', 'minq', 'stepq', 'age', 'downloadable', 'author', 'series', 'year',
                'delivery', 'ISBN', 'volume', 'part', 'language', 'table_of_contents', 'performed_by', 'performance_type',
                'storage', 'format', 'recording_length',
            ],
            'artist' => [
                'minq', 'manufacturer_warranty', 'stepq', 'adult', 'age', 'year', 'media', 'artist', 'downloadable',
                'starring', 'director', 'originalName', 'country',
            ],
            'tour' => [
                'minq', 'stepq', 'manufacturer_warranty', 'age', 'adult', 'country', 'worldRegion', 'region', 'dataTour',
                'hotel_stars', 'room', 'meal', 'price_min', 'price_max', 'downloadable', 'options',
            ],
            'event' => [
                'manufacturer_warranty', 'minq', 'stepq', 'adult', 'age', 'hall', 'hall_part', 'downloadable',
                'is_premiere', 'is_kids',
            ],
            'medicine' => ['vendorCode', 'vendor'],
        ];

        $p_all = [
            'sales_notes', 'country_of_origin', 'barcode', 'cpa', 'param', 'pickup', 'delivery', 'store', 'vat',
            'expiry', 'weight', 'dimensions',
        ]; // методы для всех

        // допустимые элементы
        $this->permitted = array_merge($p[$offerType], $p_all);

    }


    public function setAttribute(string $name, string $value)
    {
        $this->domElement->setAttribute($name, $value);

        return $this;
    }

    public function appendChild(\DOMNode $node)
    {
        $this->domElement->appendChild($node);
    }


    protected function check($expr, $msg)
    {
        if ($expr) {
            throw new \RuntimeException($msg);
        }
    }


    protected function validate(string $name, $value)
    {

    }


    public function saveXML()
    {
        return $this->domDocument->saveXML($this->domElement);
    }


    public function available($val = TRUE)
    {
        $this->check(!is_bool($val), "available должен быть boolean");
        $this->setAttribute('available', ($val) ? 'true' : 'false');

        return $this;
    }

    public function bid(int $bid)
    {
        $this->setAttribute('bid', $bid);

        return $this;
    }

    public function cbid(int $cbid)
    {
        $this->setAttribute('cbid', $cbid);

        return $this;
    }

    public function fee($fee)
    {
        $this->check(!is_int($fee), 'fee должен быть integer');
        $this->setAttribute('fee', $fee);

        return $this;
    }

    public function url($url)
    {
        $this->addNodeStr('url', $url, 512);

        return $this;
    }

    public function addNodeStr(string $name, $val, $limit)
    {
        $this->check($limit && (mb_strlen($val, $this->xmlEncoding) > $limit), "$name должен быть короче $limit символов");

        return $this->addNode($name, $val);
    }

    /**
     * @param $name
     * @param $val
     * @param array|null $attrs
     *
     * @return \DomElement
     *
     * @throws DOMException
     */
    public function addNode($name, $val = null, ?array $attrs = []): \DomElement
    {
        if (is_bool($val)) {
            $val = $val ? 'true' : 'false';
        }
        $newEl = (($val === null) ? new \DomElement($name) : new \DomElement($name, $val));
        $this->domElement->appendChild($newEl);
        if (!empty($attrs)) {
            foreach ($attrs as $k => $v) {
                $newEl->setAttribute($k, $v);
            }
        }
        return $newEl;
    }

    /**
     * @param $oldPrice
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function oldPrice($oldPrice): static
    {
        $this->check((!is_int($oldPrice)) || ($oldPrice < 1), "oldprice должен быть целым положительным числом > 0");
        $this->addNode('oldprice', $oldPrice);

        return $this;
    }

    public function dlvOption($cost, $days, $before = -1)
    {
        $dlvs = $this->domElement->getElementsByTagName('delivery-options');

        if (!$dlvs->length) {
            $dlv = new \DomElement('delivery-options');
            $this->domElement->appendChild($dlv);
        }
        else {
            $dlv = $dlvs->item(0);
            $opts = $dlv->getElementsByTagName('option');
            $this->check($opts->length >= 5, "максимум 5 опций доставки");
        }

        $this->check(!is_int($cost) || $cost < 0, "cost должно быть целым и положительным");
        $this->check(preg_match("/[^0-9\-]/", $days), "days должно состоять из цифр и тирэ");
        $this->check(!is_int($before) || $before > 24, "order-before должно быть целым и меньше 25");

        $opt = new \DomElement('option');
        $dlv->appendChild($opt);

        $opt->setAttribute('cost', $cost);
        $opt->setAttribute('days', $days);

        if ($before >= 0) {
            $opt->setAttribute('order-before', $before);
        }

        return $this;
    }

    /**
     * @param string|null $txt
     * @param bool|null $tags
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function description(?string $txt, ?bool $tags = false): YmlOfferAbstract
    {
        if ($txt) {
            $this->check(mb_strlen($txt, $this->xmlEncoding) > 3000, "description должен быть короче 3000 символов");
            if ($tags) {
                $cdata = new \DOMCdataSection($txt);
                $desc = new \DomElement('description');
                $this->domElement->appendChild($desc);
                $desc->appendChild($cdata);
            }
            else {
                $this->addNode('description', $txt);
            }
        }

        return $this;
    }


    public function inStock(int $value)
    {
        $list = $this->getElementsByTagName('outlet');
        if ($list->length) {
            $outlet = $list->item(0);
            $outlet->setAttribute('outlet', $value);
        }
        else {
            $outlet = $this->createElement('outlet');
            $outlet->setAttribute('outlet', $value);
            $outlet->setAttribute('id', 1);
            $this->addNode('outlets');
            $outlets = $this->getElementsByTagName('outlets')->item(0);
            $outlets->appendChild($outlet);
        }
    }

    /**
     * @param float $val
     * @param int|null $decimals
     *
     * @return string
     */
    protected function floatStr(float $val, ?int $decimals = 3): string
    {
        return number_format($val, $decimals, '.', '');
    }

    public function __call($method, $args)
    {
        if (array_key_exists($method, $this->aliases)) {
            $method = $this->aliases[$method];
        }

        $this->check(!in_array($method, $this->permitted), "$method вызван при типе товара {$this->offerType}");

        // значения, которые просто добавляем
        if (
            in_array($method, [
                'model', 'series', 'author', 'vendorCode', 'vendor', 'expiry', 'rec',
                'typePrefix', 'country_of_origin', 'ISBN', 'volume', 'part', 'language', 'binding', 'table_of_contents', 'performed_by',
                'performance_type', 'storage', 'format', 'recording_length', 'artist', 'media', 'starring', 'director', 'originalName', 'country', 'worldRegion', 'region', 'dataTour'
                , 'hotel_stars', 'room', 'meal', 'price_min', 'price_max', 'options', 'hall', 'hall_part', 'is_premiere', 'is_kids', 'vat',
            ])
        ) {
            $this->addNode($method, $args[0]);

            return $this;
        }

        // флаги
        if (in_array($method, ['downloadable', 'adult', 'store', 'pickup', 'delivery', 'manufacturer_warranty'])) {
            if (!isset($args[0])) {
                $args[0] = TRUE;
            }
            $this->addNode($method, ($args[0]) ? 'true' : 'false');

            return $this;
        }

        $method = '_' . $method;
        $this->$method($args);

        return $this;
    }

    /**
     * @param bool|null $flag
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function pickup(?bool $flag = false): YmlOfferAbstract
    {
        $this->addNode('pickup', ($flag ? 'true' : 'false'));

        return $this;
    }

    /**
     * @param bool|null $flag
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function store(?bool $flag = false): YmlOfferAbstract
    {
        $this->addNode('store', ($flag ? 'true' : 'false'));

        return $this;
    }

    /**
     * @param float $w
     * @param float $h
     * @param float $d
     * @param string|null $unit
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function dimensions(float $w, float $h, float $d, ?string $unit = null): YmlOfferAbstract
    {
        $attrs = [];
        if ($unit) {
            $attrs = ['unit', $unit];
        }
        $val = $this->floatStr($w, 2) . '/' . $this->floatStr($h, 2) . '/' . $this->floatStr($d, 2);
        $this->addNode('dimensions', $val, $attrs);

        return $this;
    }

    /**
     * @param float $weight
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function weight(float $weight): YmlOfferAbstract
    {
        $this->addNode('weight', $this->floatStr($weight));

        return $this;
    }


    public function _minq($args)
    {
        $this->check(!is_int($args[0]) || $args[0] < 1, "min-quantity должен содержать только цифры");
        return $this->addNode('min-quantity', $args[0]);
    }

    public function _stepq($args)
    {
        $this->check(!is_int($args[0]) || $args[0] < 1, "step-quantity должен содержать только цифры");
        return $this->addNode('step-quantity', $args[0]);
    }

    public function _page_extent($args)
    {
        $this->check(!is_int($args[0]), "page_extent должен содержать только цифры");
        $this->check($args[0] < 0, "page_extent должен быть положительным числом");

        return $this->addNode('page_extent', $args[0]);
    }

    public function _sales_notes($args)
    {
        return $this->addNodeStr('sales_notes', $args[0], 50);
    }

    public function _age($args)
    {
        $this->check(!is_int($args[0]), 'age должен иметь тип int');

        $ageEl = new \DomElement('age', $args[0]);
        $this->appendChild($ageEl);
        $ageEl->setAttribute('unit', $args[1]);

        switch ($args[1]) {
            case 'year':
                $this->check(!in_array($args[0], [0, 6, 12, 16, 18]), 'age при age_unit=year должен быть 0, 6, 12, 16 или 18');
                break;

            case 'month':
                $this->check(($args[0] < 0) || ($args[0] > 12), 'age при age_unit=month должен быть 0<=age<=12');
                break;

            default:
                $this->check(TRUE, 'age unit должен быть month или year');
                break;
        }
        return $this;
    }

    public function _param($args)
    {
        $newEl = new \DomElement('param', $args[1]);
        $this->appendChild($newEl);
        $newEl->setAttribute('name', $args[0]);
        if (isset($args[2])) {
            $newEl->setAttribute('unit', $args[2]);
        }
        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function picture(string $url): static
    {
        $pics = $this->domElement->getElementsByTagName('picture');
        $this->check($pics->length > 10, 'Можно использовать максимум 10 картинок');
        $this->addNodeStr('picture', $url, 512);

        return $this;
    }

    public function _group_id($args)
    {
        $this->check(!is_int($args[0]), 'group_id должен содержать только цифры');
        $this->check(strlen($args[0]) > 9, 'group_id не должен быть длиннее 9 символов');
        $this->setAttribute('group_id', $args[0]);

        return $this;
    }

    /**
     * @param $barcode
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function barcode($barcode): static
    {
        $barcode = trim($barcode);
        $len = strlen($barcode);
        $this->check(!preg_match('/^[0-9]+$/', $barcode), 'barcode должен содержать только цифры');
        $this->check(!($len == 8 || $len == 12 || $len == 13), 'barcode должен содержать 8, 12 или 13 цифр');
        $this->addNode('barcode', $barcode);

        return $this;
    }

    public function _year($args)
    {
        $this->check(!is_int($args[0]), 'year должен быть int');

        return $this->addNode('year', $args[0]);
    }


    public function _cpa($args)
    {
        if (!isset($args[0])) {
            $args[0] = TRUE;
        }

        return $this->addNode('cpa', ($args[0]) ? '1' : '0');
    }


}
