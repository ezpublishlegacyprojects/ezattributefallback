<?php

// Operator autoloading

$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] = array( 'script' => 'extension/ezattributefallback/autoloads/ezattributefallbackoperator.php',
                                    'class' => 'eZAttributeFallback',
                                    'operator_names' => array( 'ezattributefallback' )
                                    );
?>