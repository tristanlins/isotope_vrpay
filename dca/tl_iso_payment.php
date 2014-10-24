<?php

/**
 * Table tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['metapalettes']['vrpay'] = array(
    'type'                      => array(
        'name',
        'label',
        'type',
    ),
    'note'                      => array(
        'note',
    ),
    'config'                    => array(
        'new_order_status',
        'minimum_total',
        'maximum_total',
        'countries',
        'shipping_modules',
        'product_types',
    ),
    'gateway'                   => array(
        'trans_type',
        'vrpay_partnerno',
        'vrpay_user',
        'vrpay_password',
        'vrpay_brands',
        'vrpay_page_layout',
        'vrpay_page_receipt',
        'vrpay_shop_urlterms',
    ),
    'vrpay_note_to_payee'       => array(
        'vrpay_adddata1',
        'vrpay_adddata2',
        'vrpay_adddata3',
        'vrpay_adddata4',
        'vrpay_adddata5',
        'vrpay_infotext',
    ),
    'vrpay_payment_information' => array(
        'vrpay_addinfo1',
        'vrpay_addinfo2',
        'vrpay_addinfo3',
        'vrpay_addinfo4',
        'vrpay_addinfo5',
    ),
    'price'                     => array(
        'price',
        'tax_class',
    ),
    'enabled'                   => array(
        'debug',
        'enabled',
    ),
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_partnerno']     = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_partnerno'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'mandatory'      => true,
        'maxlength'      => 255,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_user']          = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_user'],
    'default'   => 'sendpay',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'disabled'       => true,
        'maxlength'      => 255,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_password']      = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_password'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'mandatory'      => true,
        'maxlength'      => 255,
        'decodeEntities' => true,
        'hideInput'      => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_brands']        = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_brands'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => array('VISA', 'ECMC', 'DINERS', 'AMEX', 'JCB', 'GIROPAY', 'SEPADD'),
    'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_brands'],
    'eval'      => array(
        'mandatory' => true,
        'multiple'  => true,
        'tl_class'  => 'clr'
    ),
    'sql'       => 'text NULL'
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_page_layout']   = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_page_layout'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('A1', 'B1'),
    'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_page_layout'],
    'eval'      => array(
        'includeBlankOption' => true,
        'tl_class'           => 'w50'
    ),
    'sql'       => 'char(2) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_page_receipt']  = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_page_receipt'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array(
        'tl_class' => 'w50 m12'
    ),
    'sql'       => 'char(1) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_shop_urlterms'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_shop_urlterms'],
    'exclude'   => true,
    'inputType' => 'pageTree',
    'eval'      => array(
        'mandatory' => true,
        'tl_class'  => 'clr'
    ),
    'sql'       => 'int(10) NOT NULL default \'0\''
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_adddata1'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_adddata1'],
    'default'   => 'RE ##id##',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'mandatory'      => true,
        'maxlength'      => 25,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_adddata2'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_adddata3'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 25,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_adddata3'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_adddata3'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 25,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_adddata4'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_adddata4'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 25,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_adddata5'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_adddata5'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 25,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_infotext'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_infotext'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 1024,
        'decodeEntities' => true,
        'tl_class'       => 'clr long'
    ),
    'sql'       => 'text NULL'
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_addinfo1'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_addinfo1'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 254,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_addinfo2'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_addinfo3'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 254,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_addinfo3'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_addinfo3'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 254,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_addinfo4'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_addinfo4'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 254,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['vrpay_addinfo5'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_payment']['vrpay_addinfo5'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array(
        'maxlength'      => 254,
        'decodeEntities' => true,
        'tl_class'       => 'w50'
    ),
    'sql'       => 'varchar(255) NOT NULL default \'\''
);
