<?php

namespace spec\Nonce;

use PhpSpec\ObjectBehavior;

use Nonce\Nonce;
use Nonce\Config\Config;
use Nonce\HashStore\Cookie;

class NonceSpec extends ObjectBehavior
{
  private function defaultConstruct()
  {
    $this->beConstructedWith(new \Nonce\Config\Config);
  }

  function it_is_initializable()
  {
    $this->defaultConstruct();

    $this->shouldHaveType(Nonce::class);
  }

  function it_can_test_nonce_creation()
  {
    $this->defaultConstruct();

    $this->create('user-action')
      ->shouldMatch('/^[\w|\W|\d]*\:[\w|\d|-]*\:[\d]*\:[\w|\d]*$/');
  }

  function it_can_test_nonce_verification()
  {
    $this->defaultConstruct();

    $this->verify(
      $this->create('signup-form'),
      'signup-form'
    )->shouldBe(true);

    $this->verify(
      $this->create('download-ebook'),
      'install-software'
    )->shouldBe(false);
  }

  function it_can_test_random_lib()
  {
    $this->defaultConstruct();

    $this->getRandomCharacter(16)->shouldMatch("/^[^\s]{16}$/");
    $this->getRandomCharacter(20)->shouldMatch("/^[^\s]{20}$/");
  }

  public function getMatchers(): array
  {
    return [
      'beInArray' => 'in_array',
    ];
  }
}
