<?php

require_once __DIR__ . '/Level.php';

final class LevelHolder {

  private function __construct() {
  }

  static function getLevels(): array {
    $tr1Levels = [
      ['Caves', 'caves1'],
      ['City of Vilcabamba', 'vilca1', 'vilcabamba', 'city vilcabamba', 'vilca'],
      ['The Lost Valley', 'valley1', 'lost valley', 'valley'],
      ['Tomb of Qualopec', 'qualo1', 'qualopec', 'tomb qualopec', 'qual'],
      ['St. Francis\' Folly', 'francis1', 'st francis', 'francis', 'st. francis', 'st. francis folly', 'folly', 'st francis folly'],
      ['Colosseum', 'col1', 'coloseum', 'colos'],
      ['Palace Midas', 'midas1', 'midas'],
      ['Cistern', 'cistern1'],
      ['Tomb of Tihocan', 'tihocan1', 'tihocan'],
      ['City of Khamoon', 'citykha1', 'city of kamoon', 'city khamoon'],
      ['Obelisk of Khamoon', 'obxkha1', 'obelisk', 'obelisk khamoon', 'obelisk of kamoon'],
      ['Sanctuary of the Scion', 'sanct1', 'scion', 'sanctuary scion'],
      ['Natla\'s Mines', 'natla1', 'natla', 'natla mines'],
      ['Atlantis', 'atlan1'],
      ['The Great Pyramid', 'grtpyr1', 'great pyramid', 'pyramid']
    ];

    $tr2Levels = [
      ['Great Wall', 'wall2', 'gre', 'wall', 'the great wall'],
      ['Venice', 'venice2', 'ven'],
      ['Bartoli\'s Hideout', 'bartoli2', 'bartoli', 'hideout', 'bartolis hideout', 'bartoli hideout'],
      ['Opera House', 'opera2', 'opera', 'ope'],
      ['Offshore Rig', 'rig2', 'off', 'rig', 'offshore'],
      ['Diving Area', 'divearea2', 'div', 'diving', 'dive'],
      ['40 Fathoms', 'fathoms2', 'fathoms', 'fat', 'fath'],
      ['Wreck of the Maria Doria', 'wreck2', 'wreck', 'wre', 'maria doria', 'maria'],
      ['Living Quarters', 'living2', 'liv', 'living', 'quarters', 'quart'],
      ['The Deck', 'deck2', 'deck', 'dec'],
      ['Tibetan Foothills', 'foothills2', 'foothills', 'tib', 'tibet'],
      ['Barkhang Monastery', 'barkhang2', 'barkhang', 'monastery', 'bark'],
      ['Catacombs of the Talion', 'catacombs2', 'catacombs', 'cat', 'talion', 'catacombs of talion', 'catacombs talion'],
      ['Ice Palace', 'icepal2', 'ice', 'palace', 'icepalace'],
      ['Temple of Xian', 'xian2', 'xian', 'temple xian'],
      ['Floating Islands', 'fltisl2', 'floating', 'islands', 'flo'],
      ['The Dragon\'s Lair', 'draglair2', 'dragon', 'lair', 'dragon lair', 'dragons lair', 'the dragon lair', 'the dragons lair'],
      ['Home Sweet Home', 'home2', 'hsh', 'home', 'sweet', 'sweet home']
    ];

    $tr3Levels = [
      ['Jungle', 'jun', 'jung'],
      ['Temple Ruins', 'rui', 'ruins'],
      ['The River Ganges', 'riv', 'ganges', 'river ganges'],
      ['Caves of Kaliya', 'cav', 'kaliya', 'kaliyah'],
      ['Nevada Desert', 'nev', 'nevada', 'desert'],
      ['High Security Compound', 'hig', 'hsc', 'compound', 'high'],
      ['Area 51', 'are', 'area', 'area51'],
      ['Coastal Village', 'coa', 'coastal', 'coast', 'costal'],
      ['Crash Site', 'cra', 'crash', 'crashsite'],
      ['Madubu Gorge', 'mad', 'madubu', 'madobo'],
      ['Temple of Puna', 'pun', 'puna', 'temple puna'],
      ['Thames Wharf', 'tha', 'thames', 'tames', 'wharf'],
      ['Aldwych', 'ald', 'aldywch'],
      ['Lud\'s Gate', 'lud', 'luds gate', 'luds', 'lud\'s', 'ludgate'],
      ['City', 'cit'],
      ['Antarctica', 'ant', 'antartica'],
      ['RX-Tech Mines', 'rxt', 'rx', 'rxtech', 'rx tech mines', 'rx tech', 'rx-tech'],
      ['Lost City of Tinnos', 'los', 'lost', 'tin', 'tinnos'],
      ['Meteorite Cavern', 'met', 'meteor', 'meteorite', 'cavern', 'willard'],
      ['All Hallows', 'all', 'hallows', 'all hallow']
    ];

    $levels = [];
    foreach ($tr1Levels as $tr1Level) {
      $name = array_shift($tr1Level);
      $levels[] = new Level($name, 'TR1', $tr1Level);
    }
    foreach ($tr2Levels as $tr2Level) {
      $name = array_shift($tr2Level);
      $levels[] = new Level($name, 'TR2', $tr2Level);
    }
    foreach ($tr3Levels as $tr3Level) {
      $name = array_shift($tr3Level);
      $levels[] = new Level($name, 'TR3', $tr3Level);
    }
    return $levels;
  }

  static function findLevel(string $nameLower): ?Level {
    foreach (self::getLevels() as $level) {
      if (strtolower($level->name) === $nameLower) {
        return $level;
      } else if (in_array($nameLower, $level->aliases)) {
        return $level;
      }
    }
    return null;
  }
}
