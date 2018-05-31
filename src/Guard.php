<?php
namespace Anggarasaja\Permission;

use Illuminate\Support\Collection;

/**
 * Class Guard
 * @package Anggarasaja\Permission
 */
class Guard
{
    /**
     * return collection of (guard_name) property if exist on class or object
     * otherwise will return collection of guards names that exists in config/auth.php.
     *
     * @param $model
     *
     * @return Collection
     * @throws \ReflectionException
     */
    public function getNames($model)
    {
        if (\is_object($model)) {

            if (!empty($model->guard_name)) $name = $model->guard_name;
            else $name = null;

            $guardName = $name;
        }

        if (! isset($guardName)) {

            $class = \is_object($model) ? \get_class($model) : $model;

            $tmp_name = (new \ReflectionClass($class))->getDefaultProperties();
            // var_dump(isset($tmp_name['guard_name']));
            // exit;

            if (!empty($tmp_name['guard_name'])) $name = (new \ReflectionClass($class))->getDefaultProperties()['guard_name'];
            else $name = null;

            $guardName = $name;
        }

        if ($guardName) {
            return collect($guardName);
        }
        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return;
                }
                return config("auth.providers.{$guard['provider']}.model");
            })
            ->filter(function ($model) use ($class) {
                return $class === $model;
            })
            ->keys();
    }

    /**
     * Return Default Guard name
     *
     * @param $class
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getDefaultName($class)
    {
        $default = config('auth.defaults.guard');
        return $this->getNames($class)->first() ?: $default;
    }
}
