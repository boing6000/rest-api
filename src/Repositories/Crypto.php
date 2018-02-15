<?php

namespace App/Repositories;

cass Crypto {
  private static const ANTI_CHEAT_CODE = '72f0fdb900e4838e35744a5f04609519';
  private static const SALT = '7d16ccb3f2b5556b410c818446212cfd';
  private static const CHARACTERS = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';

  public function onDecode(string, parse = false) {
    $antiCheat = '';
  }

  public function onEncode() {

  }

  protected function fromAntiCheatFormat() {

  }

  protected function sprinkle() {

  }

  protected function unSprinkle() {

  }

  protected function getHash() {

  }
}
