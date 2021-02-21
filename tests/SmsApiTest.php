<?php
# Copyright (c) 2013-2016, OVH SAS.
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
#   * Redistributions of source code must retain the above copyright
#     notice, this list of conditions and the following disclaimer.
#   * Redistributions in binary form must reproduce the above copyright
#     notice, this list of conditions and the following disclaimer in the
#     documentation and/or other materials provided with the distribution.
#   * Neither the name of OVH SAS nor the
#     names of its contributors may be used to endorse or promote products
#     derived from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY OVH SAS AND CONTRIBUTORS ``AS IS'' AND ANY
# EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
# WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL OVH SAS AND CONTRIBUTORS BE LIABLE FOR ANY
# DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
# (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
# LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
# ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
# SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
/**
 * This file contains code about \Ovh\Sms\Sms class
 */

namespace Ovh\Sms;

use GuzzleHttp\Client;
use Ovh\Api;
use Ovh\Exceptions\InvalidParameterException;
use PHPUnit\Framework\TestCase;

/**
 * Test SmsApi class
 *
 * @package  Ovh
 * @category Ovh
 */
class SmsApiTest extends TestCase
{

  /**
   * @var \Ovh\Api
   */
  private $conn = null;

  /**
   * @var string
   */
  private $account = null;
  /**
   * @var array|false|string
   */
  private $application_key;
  /**
   * @var array|false|string
   */
  private $application_secret;
  private $consumer_key;
  /**
   * @var array|false|string
   */
  private $endpoint;
  /**
   * @var Client
   */
  private $client;

  /**
   * Define id to create object
   */
  protected function setUp()
  {
    $this->application_key = getenv('APP_KEY');
    $this->application_secret = getenv('APP_SECRET');
    $this->consumer_key = getenv('CONSUMER');
    $this->endpoint = getenv('ENDPOINT');

    $this->client = new Client();
  }

  /**
   * Get private and protected method to unit test it
   *
   * @param string $name
   *
   * @return \ReflectionMethod
   */
  protected static function getPrivateMethod($name)
  {
    $class = new \ReflectionClass('Ovh\Api');
    $method = $class->getMethod($name);
    $method->setAccessible(true);

    return $method;
  }

  /**
   * Get private and protected property to unit test it
   *
   * @param string $name
   *
   * @return \ReflectionProperty
   */
  protected static function getPrivateProperty($name)
  {
    $class = new \ReflectionClass('Ovh\Api');
    $property = $class->getProperty($name);
    $property->setAccessible(true);

    return $property;
  }

  /**
   * Test missing $api_endpoint
   */
  public function testMissingApiEndpoint()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage('Endpoint');
    new SmsApi($this->application_key, $this->application_secret, null, $this->consumer_key, $this->client);
  }

  /**
   * Test creating Client if none is provided
   */
  public function testClientCreation()
  {
    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    self::assertInstanceOf(Api::class, $Sms->getConnection());
  }

  /**
   * Test add sender with no account set
   */
  public function testAddSenderNoAccount()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Please set account before using this function");

    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);
    $Sms->addSender("test", "test", "test");
  }

  /**
   * Test creating new message for response and set a sender
   */
  public function testCreateMessageForResponse()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Sender is incompatible with message for response");

    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    $Message = $Sms->createMessage(true);
    self::assertInstanceOf(MessageForResponse::class, $Message);

    $Message->setSender("test");
  }

  /**
   * Test creating a new message and set a delivery date in the past
   */
  public function testSetDeliveryTimeInThePast()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Date parameter can't be in the past");

    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    $Message = $Sms->createMessage();
    self::assertInstanceOf(Message::class, $Message);

    $Message->setDeliveryDate(new \DateTime("1970-01-01 00:00:01"));
  }

  /**
   * Test creating a new message and adding a receiver that is not a number
   */
  public function testAddWrongReceiver()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Receiver parameter must be a valid international phone number");

    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    $Message = $Sms->createMessage();
    self::assertInstanceOf(Message::class, $Message);

    $Message->addReceiver("test");
  }

  /**
   * Test creating a new message and adding two times the same receiver
   */
  public function testAddReceiverMultipleTimes()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Receiver parameter has already been added to the receivers of this message");
    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    $Message = $Sms->createMessage();
    self::assertInstanceOf(Message::class, $Message);

    $Message->addReceiver("+33612345678");
    $Message->addReceiver("+33612345678");
  }

  /**
   * Test creating Sms if SmsApi is not provided
   */
  public function testSmsApiCreation()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("SmsApi parameter is empty");

    $Sms = new Sms(null, null, null);
  }

  /**
   * Test creating Sms if type is not provided
   */
  public function testSmsTypeCreation()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Type parameter is empty");

    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    $Sms = new Sms($Sms, null, null);
  }

  /**
   * Test creating Sms if id is not provided
   */
  public function testSmsIdCreation()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("Id parameter is empty");

    $Sms = new SmsApi($this->application_key, $this->application_secret, $this->endpoint, $this->consumer_key);

    $Sms = new Sms($Sms, "incoming", null);
  }

  /**
   * Test creating Sms if SmsApi is not of SmsApi type
   */
  public function testSmsApiNotApiCreation()
  {
    $this->expectException(InvalidParameterException::class);
    $this->expectExceptionMessage("SmsApi parameter must be a SmsApi object");

    $Sms = new Sms("test", "incoming", 0);
  }

}
