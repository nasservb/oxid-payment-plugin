<?php

namespace Payever\Tests;

class ThemeHelper
{
    /**
     * @return bool
     * @throws \oxSystemComponentException
     */
    public static function isOldAzureTheme()
    {
        /** @var \oxTheme $theme */
        $theme = oxNew('oxtheme');
        $theme->load('azure');

        return $theme->getId() && version_compare($theme->getInfo('version'), '1.4.0', '<');
    }
}
