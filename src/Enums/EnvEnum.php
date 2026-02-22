<?php

namespace Keysoft\HelperLibrary\Enums;

enum EnvEnum : string
{
    case PRODUCTION = 'production';
    case STAGING = 'staging';
    case DEVELOPMENT = 'development';
    case LOCAL = 'local';
}
