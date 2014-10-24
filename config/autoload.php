<?php

/**
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Isotope', 'system/modules/isotope_vrpay/library');


/**
 * Register the templates
 */
TemplateLoader::addFiles(
    array
    (
        'iso_payment_vrpay' => 'system/modules/isotope_vrpay/templates',
    )
);
