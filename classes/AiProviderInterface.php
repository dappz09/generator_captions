<?php

interface AiProviderInterface {
    /**
     * Generate a social media caption based on the product name, features, and tone.
     *
     * @param string $productName The name of the product or brand.
     * @param string $features Key features or selling points.
     * @param string $tone The desired tone of the caption (e.g., Professional, Casual, Funny).
     * @return string The generated caption or an error message.
     */
    public function generateCaption(string $productName, string $features, string $tone): string;
}
