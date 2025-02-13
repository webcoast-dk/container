<?php

declare(strict_types=1);
namespace B13\Container\Tests\Unit\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook;
use B13\Container\Hooks\Datahandler\Database;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CommandMapBeforeStartHookTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function rewriteSimpleCommandMapTest(): void
    {
        $database = $this->prophesize(Database::class);
        $copyAfterRecord = [
            'uid' => 1,
            'tx_container_parent' => 2,
            'sys_language_uid' => 0,
            'colPos' => 3
        ];
        $database->fetchOneRecord(1)->willReturn($copyAfterRecord);

        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            ['containerFactory' => null, 'tcaRegistry' => null, 'database' => $database->reveal()]
        );
        $commandMap = [
            'tt_content' => [
                4 => [
                    'copy' => -1
                ]
            ]
        ];
        // should be
        $expected = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -1,
                        'update' => [
                            'colPos' => '2-3',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $rewrittenCommandMap = $dataHandlerHook->_call('rewriteSimpleCommandMap', $commandMap);
        self::assertSame($expected, $rewrittenCommandMap);
    }
}
