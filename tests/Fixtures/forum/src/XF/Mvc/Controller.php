<?php

namespace XF\Mvc;

abstract class Controller
{
    protected App $app;

    protected $request;

    public function app(): \XF\App
    {
        return $this->app;
    }

    /**
     * @return \XF\Http\Request
     */
    public function request()
    {
        return $this->request;
    }

    public function view($viewClass = '', $templateName = '', array $params = [])
    {
        return new Reply\View();
    }

    public function redirect($url, $message = null, $type = 'temporary')
    {
        return new Reply\Redirect();
    }

    public function redirectPermanently($url, $message = null)
    {
        return new Reply\Redirect();
    }

    public function error($error, $code = 200)
    {
        return new Reply\Error();
    }

    public function message($message, $code = 200)
    {
        return new Reply\Message();
    }

    public function exception(Reply\AbstractReply $reply)
    {
        return new Reply\Exception($reply);
    }

    /**
     * @template T of \XF\ControllerPlugin\AbstractPlugin
     *
     * @param class-string<T> $name
     *
     * @return T
     */
    public function plugin($name)
    {
        return new $name($this);
    }

    /**
     * @template T of Entity\Entity
     *
     * @param class-string<T> $identifier
     * @param mixed $id
     * @param array|string|null $with
     * @param string|null $phraseKey
     *
     * @return T
     */
    public function assertRecordExists($identifier, $id, $with = null, $phraseKey = null)
    {
        return $this->app->em()->create($identifier);
    }

    /**
     * @template T of \XF\Service\AbstractService
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments
     *
     * @return T
     */
    public function service($class)
    {
        return $this->app->service(...func_get_args());
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function data($class)
    {
        return $this->app->data($class);
    }

    /**
     * @template T of Entity\Finder
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function finder($type)
    {
        return $this->app->em()->getFinder($type);
    }

    /**
     * @template T of Entity\Repository
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function repository($identifier)
    {
        return $this->app->em()->getRepository($identifier);
    }
}
