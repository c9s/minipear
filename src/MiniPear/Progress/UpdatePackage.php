<?php
namespace MiniPear\Progress;
use Phar;
use DOMDocument;
use DOMText;
use Exception;
use PharData;
use SimpleXmlElement;


/**
 *
 * MiniPear\Progress\UpdatePackage::setChannel( 'path/to/packageFile.tar', 'target.channel.net' );
 */
class UpdatePackage
{

    static function patchPackageXmlPackageDep($packageNode, $fromChannel, $toChannel) {
        if ($packageNode->getName() == "package" ) {
            if ( ((string)$packageNode->channel) == $fromChannel ) {
                $packageNode->channel = $toChannel;
            }
        }
    }

    static function patchPackageXml($xmlContent, $toChannel) 
    {
        $sxml = new SimpleXmlElement( $xmlContent );
        $fromChannel = (string) $sxml->channel;
        $sxml->channel = $toChannel;

        if ( $deps = $sxml->dependencies ) {
            if ( $required = $deps->required ) {
                foreach ( $required->children() as $child ) {
                    self::patchPackageXmlPackageDep($child, $fromChannel, $toChannel);
                }
            }
            if ( $optional = $deps->optional ) {
                foreach ( $optional->children() as $child ) {
                    self::patchPackageXmlPackageDep($child, $fromChannel, $toChannel);
                }
            }
        }
        return $sxml->asXML();
    }


    static function setChannel($packageFile,$channel)
    {

        /*
         * xxx: phar doesn't support tgz format.
         */
        try {
        $p = new PharData($packageFile, 0,'phartest.phar'); 

        # $pharName = str_replace('.tgz','.phar',$packageFile);
        # rename( $packageFile , $pharName );
        # $pharUri = 'phar://' . $pharName . '/package.xml';

        // load package.xml
        if (isset($p['package2.xml'])) {
            $xml = file_get_contents( $p['package2.xml'] );
        } else {
            $xml = file_get_contents( $p['package.xml'] );
        }

        $xml = self::patchPackageXml( $xml, $channel );

        // $xml = \MiniPear\Utils::change_package_xml_channel( $xml , $channel );

        // save package.xml
        if (isset($p['package2.xml'])) {
            $p['package2.xml'] = $xml;
        } else {
            $p['package.xml'] = $xml;
        }
        // file_put_contents( $pharUri , $xml );

        /* rename it back */
        // rename( $pharName , $packageFile );
        } catch( Exception $e ) {
            die( $e->getMessage() );
        }
    }

}


