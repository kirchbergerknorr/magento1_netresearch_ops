<?php
class ObjectHandler
{
    public static function getObject($key)
    {
        $objectsFile = dirname(__FILE__) . '/objects.xml';
        $objects = simplexml_load_file($objectsFile);
        if (!$objects->$key) {
            throw new Exception(sprintf('No serialized object "%s" available in %s', $key, $objectsFile));
        }

        return unserialize(base64_decode(current($objects->$key)));
    }
}
