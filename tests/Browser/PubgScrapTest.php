<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PubgScrapTest extends DuskTestCase
{
    public string $save_at_folder = '~/Downloads';
    public string $item_type = '';
    public string $sub_item_type = '';

    public function testExample(): void
    {
        $this->makeFolder($this->save_at_folder);
        $this->fetchAndSaveFromUrl('https://pubg.com/en/game-info/weapons/ar');
        $this->fetchAndSaveFromUrl('https://pubg.com/en/game-info/vehicles/land');
    }

    /**
     * @param $url
     * @return void
     */
    public function fetchAndSaveFromUrl($url): void
    {
        try {
            $this->browse(function (Browser $browser) use ($url) {
                $browser->visit($url);
                $this->setCookies($browser);

                $this->item_type = $this->getItemType($browser);

                foreach ($browser->elements('.game-info-tab__items.swiper-slide a') as $tab) {
                    $browser->pause(200);

                    $tab->click();

                    $browser->pause(200);

                    $this->sub_item_type = $this->getItemSubType($browser);

                    $browser->pause(100);

                    $this->saveItems($browser);
                }
            });
        } catch (\Throwable $e) {
            logger()->error('Error occurred while browsing at line : ' . $e->getLine() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param Browser $browser
     * @return void
     */
    public function setCookies(Browser $browser): void
    {
        $browser->pause(100);

        if (
            $browser->plainCookie('analytics') &&
            $browser->plainCookie('essential') &&
            $browser->plainCookie('functional')
        ) {
            return;
        }

        $browser->pause(200);

        $browser->plainCookie('analytics', 'N')
            ->plainCookie('essential', 'N')
            ->plainCookie('functional', 'N');

        $browser->refresh();
    }

    public function saveItems(Browser $browser): void
    {
        $capitalItemType = strtoupper($this->item_type);
        $this->makeFolder("$this->save_at_folder/$capitalItemType");
        $this->makeFolder("$this->save_at_folder/$capitalItemType/$this->sub_item_type");

        foreach ($browser->elements(".$this->item_type-card__image") as $itemImgElem) {
            $this->saveItem($itemImgElem);

            $browser->pause(100);
        }
    }

    public function makeFolder($path): void
    {
        if (!file_exists($path)) {
            exec("mkdir $path");
        }
    }

    public function saveItem($itemImgElem): void
    {
        $url = $itemImgElem->getAttribute('src');
        $guessed_name = $this->guessName($url);

        exec("wget $url -O $this->save_at_folder/$this->item_type/$this->sub_item_type/$guessed_name");
    }

    /**
     * @param Browser $browser
     * @return string
     */
    public function getItemType(Browser $browser): string
    {
        return strtolower($browser->element('.layout__body h1')?->getText() ?? '');
    }

    /**
     * @param Browser $browser
     * @return string
     */
    public function getItemSubType(Browser $browser): string
    {
        $itemSubTypeElem = $browser->element(".layout__page .{$this->item_type}__contents .{$this->item_type}-card__category");

        return $itemSubTypeElem?->getText() ?? '';
    }

    /**
     * @param $url
     * @return string
     */
    public function guessName($url): string
    {
        return strtoupper(last(explode("img-$this->item_type-", $url)));
    }
}
