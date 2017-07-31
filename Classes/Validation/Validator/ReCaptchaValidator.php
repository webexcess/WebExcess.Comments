<?php

namespace WebExcess\Comments\Validation\Validator;

/*
 * This file is part of the WebExcess.Comments package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Validator\AbstractValidator;

/**
 * @Flow\Scope("singleton")
 */
class ReCaptchaValidator extends AbstractValidator
{

    /**
     * @Flow\InjectConfiguration(package="WebExcess.Comments", path="reCaptcha")
     * @var array
     */
    protected $settings;

    /**
     * @param mixed $value The value that should be validated
     * @return void
     * @api
     */
    protected function isValid($value)
    {
        if (!$this->settings['enabled']) {
            return true;
        }

        if (!is_string($value)) {
            $this->addError('The given value was not a valid string.', 1501509806);
            return;
        }

        $recaptcha = new \ReCaptcha\ReCaptcha($this->settings['secretKey']);
        $resp = $recaptcha->verify($value, $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess() === false) {
            $this->addError('The captcha was not answered correctly.', 1501509812);
        }
    }

}
