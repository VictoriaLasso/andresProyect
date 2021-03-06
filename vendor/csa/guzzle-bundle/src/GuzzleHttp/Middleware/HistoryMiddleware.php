<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware;

use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

/**
 * History Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class HistoryMiddleware
{
    private $container;

    public function __construct(\SplObjectStorage $container)
    {
        $this->container = $container;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                function ($value) use ($request, $options) {
                    $info = isset($this->container[$request]) ? $this->container[$request]['info'] : null;
                    $this->container->attach($request, [
                        'response' => $value,
                        'error' => null,
                        'options' => $options,
                        'info' => $info,
                    ]);

                    return $value;
                },
                function ($reason) use ($request, $options) {
                    $info = isset($this->container[$request]) ? $this->container[$request]['info'] : null;
                    $this->container->attach($request, [
                        'response' => null,
                        'error' => $reason,
                        'options' => $options,
                        'info' => $info,
                    ]);

                    return new RejectedPromise($reason);
                }
            );
        };
    }
}
