<?php

namespace avadim\YandexYmlGenerator;


use avadim\YandexYmlGenerator\YandexYmlGenerator\YmlOfferSimple;
use DomDocument;
use DomElement;
use DOMException;
use DOMImplementation;
use RuntimeException;

class YmlDocument extends DomDocument
{
    protected $fp;
    protected string $filename = './yml.xml';
    protected ?int $bufferSize = null;
    protected ?string $date;

    protected array $shopElements = [];
    protected bool $shopSaved = false;

    public ?DomElement $currencies = null;

    public ?DomElement $categories = null;

    public ?YmlOffer $offer = null;

    protected ?string $defaultCurrency = null;



    /**
     * @param string|null $xmlVersion
     * @param string|null $xmlEncoding
     */
    public function __construct(?string $xmlVersion = null, ?string $xmlEncoding = null)
    {
        parent::__construct($xmlVersion ?: '1.0', $xmlEncoding ?: 'UTF-8');

        $this->date = date('Y-m-dTH:i');
        $this->shopElements = [
            'name' => null,
            'company' => null,
            'url' => null,
            'currencies' => null,
            'categories' => null,
        ];
    }

    public static function create(string $name, string $company, ?string $url = null)
    {
        $yml = new self();
        $yml->name($name)->company($company);

        if ($url) {
            $yml->url($url);
        }

        return $yml;
    }

    /**
     * Добавление дочернего элемента к shop
     *
     * @param string $name
     * @param $value
     *
     * @return DOMElement|false
     *
     * @throws DOMException
     */
    public function addShopNode(string $name, $value = null)
    {
        if ($value !== null) {
            $node = $this->createElement($name, $value);
        }
        else {
            $node = $this->createElement($name);
        }
        $this->shopElements[$name] = $node;

        return $node;
    }

    /**
     * @param $date
     *
     * @return $this
     */
    public function date($date)
    {
        if (is_int($date)) {
            $this->date = date('Y-m-dTH:i', $date);
        }
        else {
            $this->date = (string)$date;
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function fileName(string $name): YmlDocument
    {
        $this->filename = $name;
        $this->fp = fopen($this->filename, 'w');

        return $this;
    }

    public function bufferSize(int $size): YmlDocument
    {
        $this->bufferSize = $size;
        stream_set_write_buffer($this->fp, $this->bufferSize);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     *
     * @throws DOMException|RuntimeException
     */
    public function name(string $name): YmlDocument
    {
        $len = mb_strlen($name, $this->xmlEncoding);
        if ($len < 3) {
            throw new RuntimeException("name='$name' менее 3 символов");
        }
        elseif ($len > 23) {
            throw new RuntimeException("name='$name' длиннее 23 символов");
        }
        /**
         * TODO проверка на допустимые символы
         * Можно использовать латиницу, кириллицу, цифры и символы . , : | “ ” « » № & ' " + -.
         */
        $this->addShopNode('name', $name);

        return $this;
    }

    /**
     * @param string $company
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function company(string $company): YmlDocument
    {
        $this->addShopNode('company', $company);

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function url(string $url): YmlDocument
    {
        if (mb_strlen($url, $this->encoding) > 512) {
            $this->error('url должен быть не более 512 символов');
        }
        $this->addShopNode('url', $url);

        return $this;
    }

    public function error($text)
    {
        throw new RuntimeException($text);
    }

    /**
     * @param $name
     * @param $version
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function platform($name, $version = null): YmlDocument
    {
        $this->addShopNode('platform', $name);
        if ($version !== null) {
            $this->addShopNode('version', $version);
        }
        return $this;
    }

    /**
     * @throws DOMException
     */
    public function agency($name)
    {
        $this->addShopNode('agency', $name);

        return $this;
    }

    /**
     * @throws DOMException
     */
    public function email($mail)
    {
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->error(' Некорректный Email');
        }
        $this->addShopNode('email', $mail);

        return $this;
    }

    /**
     * @throws DOMException
     */
    public function cpa(?bool $val = true)
    {
        $this->addShopNode('cpa', $val ? '1' : '0');

        return $this;
    }

    /**
     * @param string $code
     * @param $rate
     * @param $plus
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function currency(string $code, $rate, $plus = 0)
    {
        if (strpos($rate, ',') !== FALSE) {
            $this->error("rate разделяется только точкой");
        }
        if (strpos($plus, ',') !== FALSE) {
            $this->error("plus разделяется только точкой");
        }

        $currency = $this->createElement('currency');

        $currency->setAttribute('id', $code);
        $currency->setAttribute('rate', $rate);
        if ($plus) {
            $currency->setAttribute('plus', $plus);
        }
        if (!$this->shopElements['currencies']) {
            $this->addShopNode('currencies');
            $this->defaultCurrency = $code;
        }

        $this->shopElements['currencies']->appendChild($currency);

        return $this;
    }

    /**
     * @param int $id
     * @param string $name
     * @param int|null $parentId
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function category(int $id, string $name, ?int $parentId = null): YmlDocument
    {
        if ($id < 1) {
            $this->error('id должен быть целым положительным числом > 0');
        }

        if ($parentId && $parentId < 1) {
            $this->error('parentId должен быть целым положительным числом > 0');
        }

        $category = $this->createElement('category', $name);
        $category->setAttribute('id', $id);
        if ($parentId) {
            $category->setAttribute('parentId', $parentId);
        }
        if (!$this->shopElements['categories']) {
            $this->addShopNode('categories');
        }
        $this->shopElements['categories']->appendChild($category);

        return $this;
    }

    protected function writeBegin()
    {
        $str = '<?xml version="' . $this->xmlVersion . '" encoding="' . $this->xmlEncoding . '"?>';
        $str .= '<yml_catalog date="' . $this->date . '">';
        $str .= '<shop>';
        fwrite($this->fp, $str);
        foreach ($this->shopElements as $xml) {
            $str = $xml->ownerDocument->saveXML($xml);
            fwrite($this->fp, $str);
        }
        fwrite($this->fp, '<offers>');
        $this->shopSaved = true;
    }


    protected function writeEnd()
    {
        $str = '</offers></shop></yml_catalog>';
        fwrite($this->fp, $str);
    }


    protected function writeOffer()
    {
        if ($this->offer) {
            fwrite($this->fp, $this->offer->saveXML());
            $this->offer = null;
        }
    }

    public static function getOfferClass(string $offerType)
    {
        switch (strtolower($offerType)) {
            case 'simple':
                return YmlOfferSimple::class;
            default:
                return YmlOffer::class;
        }
    }

    /**
     * @param $id
     * @param $price
     * @param $currency
     * @param $category
     * @param $offerType
     * @param $from
     *
     * @return YmlOffer
     *
     * @throws DOMException
     */
    public function newOffer($offerType, $id, $price, $currency, $category, $from): YmlOffer
    {
        if (!$this->shopSaved) {
            $this->writeBegin();
        }

        $this->writeOffer();

        if (preg_match("/[^a-z,A-Z0-9]/", $id)) {
            $this->error('id должен содержать только латинские буквы и цифры');
        }
        if (strlen($id) > 20) {
            $this->error("id длиннее 20 символов");
        }

        if ((!is_int($category)) || ($category < 1) || ($category >= pow(10, 19))) {
            $this->error("categoryId - целое число, не более 18 знаков");
        }

        if ($price && (!is_numeric($price) || $price < 0)) {
            $this->error("price должно быть целым и положительным");
        }

        if (!is_null($from)) {
            if (!is_bool($from)) {
                $this->error('from должен быть boolean');
            }
        }

        $offerClass = static::getOfferClass($offerType);
        $this->offer = new $offerClass($this, $offerType);
        $this->offer->setAttribute('id', $id);
        $this->offer->addNode('currencyId', $currency);
        $this->offer->addNode('categoryId', $category);
        if ($price) {
            $pr = $this->offer->addNode('price', $price);
            if (!is_null($from)) {
                $pr->setAttribute('from', $from ? 'true' : 'false');
            }
        }

        return $this->offer;
    }

    /**
     * @param $name
     * @param $id
     * @param $price
     * @param $currency
     * @param $category
     * @param $from
     *
     * @return YmlOffer
     *
     * @throws DOMException
     */
    public function offerSimple($name, $id, $price, $currency, $category, $from = NULL): YmlOffer
    {
        $offer = $this->newOffer('simple', $id, $price, $currency, $category, $from);
        $offer->addNodeStr('name', $name, 120);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerArbitrary($model, $vendor, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('arbitrary', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'vendor.model');
        $offer->addNode('vendor', $vendor);
        $offer->addNode('model', $model);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerBook($name, $publisher, $age, $age_u, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('book', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'book');
        $offer->addNodeStr('name', $name, 120);
        $offer->addNode('publisher', $publisher);
        $offer->age($age, $age_u);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerAudiobook($name, $publisher, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('audiobook', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'audiobook');
        $offer->addNodeStr('name', $name, 120);
        $offer->addNode('publisher', $publisher);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerArtist($title, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('artist', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'artist.title');
        $offer->addNode('title', $title);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerTour($name, $days, $included, $transport, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('tour', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'tour');
        $offer->addNode('name', $name);

        if (!is_int($days) || $days < 0) {
            $this->error("days должно быть целым и положительным");
        }
        $offer->addNode('days', $days);

        $offer->addNode('included', $included);
        $offer->addNode('transport', $transport);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerEvent($name, $place, $date, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('event', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'event-ticket');
        $offer->addNode('name', $name);
        $offer->addNode('place', $place);
        $offer->addNode('date', $date);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function offerMedicine($name, $id, $price, $currency, $category, $from = NULL)
    {
        $offer = $this->newOffer('medicine', $id, $price, $currency, $category, $from);
        $offer->setAttribute('type', 'medicine');
        $offer->addNode('name', $name);
        $offer->pickup(TRUE);
        $offer->delivery(FALSE);

        return $offer;
    }

    /**
     * @throws DOMException
     */
    public function delivery($cost, $days, $before = -1)
    {
        if (empty($this->shopElements['delivery-options'])) {
            $deliveryOptions = $this->addShopNode('delivery-options');
        }
        else {
            $deliveryOptions = $this->shopElements['delivery-options'];
        }
        $opts = $deliveryOptions->getElementsByTagName('option');
        if ($opts->length >= 5) {
            $this->error("максимум 5 опций доставки");
        }

        if (!is_int($cost) || $cost < 0) {
            $this->error("cost должно быть целым и положительным");
        }
        if (preg_match("/[^0-9\-]/", $days)) {
            $this->error("days должно состоять из цифр и тирэ");
        }
        if (!is_int($before) || $before > 24) {
            $this->error("order-before должно быть целым и меньше 25");
        }

        $opt = $this->createElement('option');

        $opt->setAttribute('cost', $cost);
        $opt->setAttribute('days', $days);

        if ($before >= 0) {
            $opt->setAttribute('order-before', $before);
        }

        $deliveryOptions->appendChild($opt);

        return $this;
    }

    public function saveAndClose()
    {
        if ($this->fp) {
            if ($this->offer) {
                $this->writeOffer();
            }
            $this->writeEnd();
            fclose($this->fp);
            $this->fp = null;
        }
    }
}

