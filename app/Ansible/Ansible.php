<?php namespace App\Ansible;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Ansible command factory
 *
 * @package App\Ansible
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class Ansible
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $inventoryFile;

    /**
     * @var string
     */
    private $playbookFile;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $dir
     * @param string $inventoryFile
     * @param string $playbookFile
     * @param array $options
     */
    public function __construct($dir, $inventoryFile, $playbookFile, array $options = [])
    {
        $this->dir = $dir;
        $this->inventoryFile = $inventoryFile;
        $this->playbookFile = $playbookFile;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return string
     */
    public function getInventoryFile()
    {
        return $this->inventoryFile;
    }

    /**
     * @return string
     */
    public function getPlaybookFile()
    {
        return $this->playbookFile;
    }

    /**
     * Play playbook
     */
    public function play()
    {
        $processBuilder = ProcessBuilder::create(["ansible-playbook", "-i", $this->inventoryFile, $this->playbookFile])
            ->setWorkingDirectory($this->dir)
            ->setTimeout(600)
            ->setEnv('ANSIBLE_FORCE_COLOR', true);

        $processBuilder->setEnv("ANSIBLE_NOCOWS", 'True');
        $processBuilder->setEnv("ANSIBLE_HOST_KEY_CHECKING", 'False');
        $processBuilder->setEnv("ANSIBLE_DEPRECATION_WARNINGS", false);
        $processBuilder->setEnv("ANSIBLE_ROLES_PATH", base_path("resources/ansible/roles"));
        $processBuilder->setEnv("ANSIBLE_CALLBACK_PLUGINS", base_path("resources/ansible/callback_plugins"));
        $processBuilder->setEnv("ANSIBLE_LOOKUP_PLUGINS", base_path("resources/ansible/lookup_plugins"));
        $processBuilder->setEnv("ANSIBLE_LIBRARY", base_path("resources/ansible/modules"));
        $processBuilder->setEnv("ANSIBLE_FILTER_PLUGINS", base_path("resources/ansible/filter_plugins"));

        if (!empty($this->options['pipelining'])) {
            $processBuilder->setEnv('ANSIBLE_SSH_PIPELINING', true);
        }

        if (!empty($this->options['private_key'])) {
            $processBuilder->setEnv("ANSIBLE_PRIVATE_KEY_FILE", $this->options['private_key']);
        }

        return $processBuilder->getProcess();
    }
}
