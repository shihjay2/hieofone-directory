<?php

namespace SocialiteProviders\Doximity;

use SocialiteProviders\Manager\SocialiteWasCalled;

class DoximityExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('doximity', __NAMESPACE__.'\Provider');
    }
}
