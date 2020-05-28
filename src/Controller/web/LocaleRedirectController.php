<?php

namespace App\Controller\web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LocaleRedirectController extends AbstractController
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $availableLocales;

    public function __construct(string $defaultLocale, string $availableLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->availableLocales = explode('|', $availableLocales);
    }

    public function __invoke(string $route, Request $request): RedirectResponse
    {
        $localeHeader = $request->headers->get('accept-language');
        if (null !== $localeHeader) {
            $locale = substr(
                locale_accept_from_http($localeHeader),
                0,
                2);
            if (!in_array($locale, $this->availableLocales)) {
                $locale = $this->defaultLocale;
            }
        } else {
            $locale = $this->defaultLocale;
        }

        if (!in_array($this->defaultLocale, $this->availableLocales)) {
            $locale = $this->availableLocales[0];
        }

        $handle=fopen("test.txt","w+");
        fwrite($handle,$route);

        return $this->redirectToRoute($route, [
            '_locale' => $locale ?? $this->defaultLocale,
        ]);
    }
}
