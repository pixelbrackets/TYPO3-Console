<?php
namespace Helhum\Typo3Console\Tests\Functional\Command;

/*
 * This file is part of the TYPO3 Console project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

use Helhum\Typo3Console\Mvc\Cli\FailedSubProcessCommandException;

class BackendCommandControllerTest extends AbstractCommandTest
{
    /**
     * @test
     */
    public function backendCanBeLockedAndUnlocked()
    {
        $output = $this->executeConsoleCommand('backend:lock');
        $this->assertContains('Backend has been locked', $output);
        $output = $this->executeConsoleCommand('backend:lock');
        $this->assertContains('Backend is already locked', $output);
        $output = $this->executeConsoleCommand('backend:unlock');
        $this->assertContains('Backend lock is removed', $output);
        $output = $this->executeConsoleCommand('backend:unlock');
        $this->assertContains('Backend is already unlocked', $output);
    }

    /**
     * @test
     */
    public function backendCanBeLockedAndUnlockedForEditors()
    {
        $output = $this->executeConsoleCommand('backend:lockforeditors');
        $this->assertContains('Locked backend for editor access', $output);
        $output = $this->executeConsoleCommand('backend:lockforeditors');
        $this->assertContains('The backend was already locked for editors', $output);
        $output = $this->executeConsoleCommand('backend:unlockforeditors');
        $this->assertContains('Unlocked backend for editors', $output);
        $output = $this->executeConsoleCommand('backend:unlockforeditors');
        $this->assertContains('The backend was not locked for editors', $output);
    }

    /**
     * @test
     */
    public function adminUserWithTooShortUsernameWillBeRejected()
    {
        try {
            $this->commandDispatcher->executeCommand('backend:createadmin', ['foo', 'bar']);
            $this->fail('Command did not fail as expected (user is created)');
        } catch (FailedSubProcessCommandException $e) {
            $this->assertContains('Username must be at least 4 characters', $e->getOutputMessage());
        }
    }

    /**
     * @test
     */
    public function adminUserWithTooShortPasswordWillBeRejected()
    {
        try {
            $this->commandDispatcher->executeCommand('backend:createadmin', ['foobar', 'baz']);
            $this->fail('Command did not fail as expected (user is created)');
        } catch (FailedSubProcessCommandException $e) {
            $this->assertContains('Password must be at least 8 characters', $e->getOutputMessage());
        }
    }

    /**
     * @test
     */
    public function adminUserWithValidCredentialsWillBeCreated()
    {
        $output = $this->executeConsoleCommand('backend:createadmin', ['administrator', 'password']);
        $this->assertContains('Created admin user with username "administrator"', $output);
        $queryResult = $this->executeMysqlQuery('SELECT username FROM be_users WHERE username="administrator"');
        $this->assertSame('administrator', trim($queryResult));
    }

    /**
     * @test
     */
    public function adminUserWithValidCredentialsWillNotBeCreatedIfUsernameAlreadyExists()
    {
        try {
            $this->commandDispatcher->executeCommand('backend:createadmin', ['administrator', 'password2']);
            $this->fail('Command did not fail as expected (user is created)');
        } catch (FailedSubProcessCommandException $e) {
            $this->assertContains('A user with username "administrator" already exists', $e->getOutputMessage());
        }
    }
}
