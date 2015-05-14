<?php

namespace app\components\helpers;

class ViewHelper
{
	public static function getClientNameBySource($source)
	{
        switch ($source) {
        	case 'vkontakte':
        	    $name = 'Вконтакте';
        	    break;

        	case 'facebook':
        	    $name = 'Facebook';
        	    break;

        	case 'linkedin':
        	    $name = 'LinkedIn';
        	    break;

        	case 'google':
        	    $name = 'Google+';
        	    break;

        	case 'twitter':
        	    $name = 'Twitter';
        	    break;

        	default:
        	    $name = 'Неизвестно';
        	    break;
        }

        return $name;
	}
}