<?php

/**
 * This file is part of the DigitalOcean library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DigitalOcean\CLI\SSHKeys;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DigitalOcean\CLI\Command;

/**
 * Command-line ssh-keys:edit class.
 *
 * @author Antoine Corcy <contact@sbin.dk>
 */
class EditCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('ssh-keys:edit')
            ->addArgument('id', InputArgument::REQUIRED, 'The SSH key id to edit')
            ->addArgument('ssh_pub_key', InputArgument::REQUIRED, 'The new public SSH key')
            ->setDescription('Edit an existing public SSH key in your account')
            ->addOption('credentials', null, InputOption::VALUE_REQUIRED,
                'If set, the yaml file which contains your credentials', COMMAND::DEFAULT_CREDENTIALS_FILE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getHelperSet()->get('dialog')->askConfirmation(
            $output,
            sprintf('<question>Are you sure to edit this SSH key %s ? (y/N)</question> ', $input->getArgument('id')),
            false
        )) {
            $output->writeln('Aborted!');

            return;
        }

        $digitalOcean = $this->getDigitalOcean($input->getOption('credentials'));
        $sshKey       = $digitalOcean->sshKeys()->edit(
            $input->getArgument('id'),
            array('ssh_pub_key' => $input->getArgument('ssh_pub_key'))
        );

        $result[] = sprintf('status: <value>%s</value>', $sshKey->status);
        $result[] = sprintf('id:     <value>%s</value>', $sshKey->ssh_key->id);
        $result[] = sprintf('name:   <value>%s</value>', $sshKey->ssh_key->name);
        $result[] = sprintf('key:    <value>%s</value>', $sshKey->ssh_key->ssh_pub_key);

        $output->getFormatter()->setStyle('value', new OutputFormatterStyle('green', 'black'));
        $output->writeln($result);
    }
}
