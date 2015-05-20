<?php
/**
 * Playbook Renderer
 */

namespace Phansible\Renderer;

class PlaybookRenderer extends TemplateRenderer
{
    /** @var string */
    protected $varsFilename;

    /** @var array Playbook Roles */
    protected $roles = [];

    /** @var array Variables to prompt for */
    protected $varsPrompt = [];

    /**
     * {@inheritdoc}
     */
    public function loadDefaults()
    {
        $this->setTemplate('playbook.yml.twig');
        $this->setFilePath('ansible/playbook.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [
            'varsfile' => $this->varsFilename,
            'roles'  => $this->roles,
            'varsprompt' => $this->varsPrompt
        ];
    }

    /**
     * @param string $varsFilename
     */
    public function setVarsFilename($varsFilename)
    {
        $this->varsFilename = $varsFilename;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles = [])
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string $role
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    /**
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return array_search($role, $this->roles) !== false;
    }

    /**
     * @param array $variables
     */
    public function addVarPrompts($variables)
    {
        $this->varsPrompt = array_merge($this->varsPrompt, $variables);
    }
}
