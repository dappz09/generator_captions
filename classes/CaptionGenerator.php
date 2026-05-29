<?php

require_once 'AiProviderInterface.php';

class CaptionGenerator {
    private $provider;

    public function __construct(AiProviderInterface $provider) {
        $this->provider = $provider;
    }

    public function setProvider(AiProviderInterface $provider) {
        $this->provider = $provider;
    }

    public function generate(string $productName, string $features, string $tone): string {
        return $this->provider->generateCaption($productName, $features, $tone);
    }
}
