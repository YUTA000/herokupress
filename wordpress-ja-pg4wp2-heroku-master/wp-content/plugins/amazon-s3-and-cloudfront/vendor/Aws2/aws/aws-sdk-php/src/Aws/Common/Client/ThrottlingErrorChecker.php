<?php

/**
 * Copyright 2010-2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client;

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\Parser\ExceptionParserInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffStrategyInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractBackoffStrategy;
/**
 * Backoff logic that handles throttling exceptions from services
 */
class ThrottlingErrorChecker extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractBackoffStrategy
{
    /** @var array Whitelist of exception codes (as indexes) that indicate throttling */
    protected static $throttlingExceptions = array('RequestLimitExceeded' => true, 'Throttling' => true, 'ThrottlingException' => true, 'ProvisionedThroughputExceededException' => true, 'RequestThrottled' => true);
    /**
     * @var ExceptionParserInterface Exception parser used to parse exception responses
     */
    protected $exceptionParser;
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\Parser\ExceptionParserInterface $exceptionParser, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffStrategyInterface $next = null)
    {
        $this->exceptionParser = $exceptionParser;
        if ($next) {
            $this->setNext($next);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function makesDecision()
    {
        return true;
    }
    /**
     * {@inheritdoc}
     */
    protected function getDelay($retries, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response = null, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException $e = null)
    {
        if ($response && $response->isClientError()) {
            $parts = $this->exceptionParser->parse($request, $response);
            return isset(self::$throttlingExceptions[$parts['code']]) ? true : null;
        }
    }
}
