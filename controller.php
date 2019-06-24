<?php

namespace Concrete\Package\HwCommunityStoreGoogleAnalytics;

use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected $pkgHandle = 'hw_community_store_google_analytics';
    protected $appVersionRequired = '5.7.1';
    protected $pkgVersion = '0.9.1';

    public function getPackageDescription()
    {
        return t("Add Google Analytics Ecommerce to the community store");
    }

    public function getPackageName()
    {
        return t("Google Ecommerce Analytics");
    }

    protected $pkgAutoloaderRegistries = array(

    );

    public function on_start()
    {
        $this->app->bind(\Concrete\Package\CommunityStore\Controller\SinglePage\Checkout\Complete::class, function (\Concrete\Core\Application\Application $app, array $parameters) {
            return $app->make(\Concrete\Package\HwCommunityStoreGoogleAnalytics\Src\Order::class, $parameters);
        });


    }

    public function install()
    {
        $installed = Package::getInstalledHandles();
        if (!(is_array($installed) && in_array('community_store', $installed))) {
            throw new ErrorException(t('This package requires that Community Store be installed'));
        } else {
            parent::install();
        }
    }

    public function uninstall()
    {

       parent::uninstall();
    }

}
