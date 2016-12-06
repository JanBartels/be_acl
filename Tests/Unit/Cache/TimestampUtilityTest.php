<?php
namespace JBartels\BeAcl\Tests\Unit\Cache;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Alexander Stehlik (astehlik.deleteme@intera.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Unit tests for the timestamp utility.
 */
class TimestampUtilityTest extends UnitTestCase {

	/**
	 * @var \JBartels\BeAcl\Cache\TimestampUtility
	 */
	protected $timestampUtility;

	/**
	 * Initializes the timestamp utility
	 */
	public function setUp() {
		$this->timestampUtility = $this->getMock('JBartels\\BeAcl\\Cache\\TimestampUtility', array('initializeCache'));
		$this->initializeTimestampCache();
	}

	/**
	 * @test
	 */
	public function newerTimestampThanInCacheIsInvalid() {
		$this->timestampUtility->updateTimestamp();
		$isValid = $this->timestampUtility->permissionTimestampIsValid(time() + 100);
		$this->assertTrue($isValid);
	}

	/**
	 * @test
	 */
	public function olderTimestampThanInCacheIsInvalid() {
		$this->timestampUtility->updateTimestamp();
		$isValid = $this->timestampUtility->permissionTimestampIsValid(time() - 100);
		$this->assertFalse($isValid);
	}

	/**
	 * Initializes the cache mock in the timestamp utility.
	 */
	protected function initializeTimestampCache() {
		/** @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheMock */
		$cacheMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\Frontend\\FrontendInterface', array(), array(), '', FALSE);
		$this->timestampUtility->setTimestampCache($cacheMock);
	}
}