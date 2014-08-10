<?php

namespace Phansible\Controller;

use Flint\Controller\Controller;
use Phansible\Application;
use Phansible\Model\VagrantBundle;
use Phansible\Renderer\PlaybookRenderer;
use Phansible\Renderer\VagrantfileRenderer;
use Phansible\Renderer\VarfileRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Phansible
 */
class BundleController extends Controller
{

    public function indexAction(Request $request, Application $app)
    {
        $vagrant = new VagrantBundle($this->get('ansible.path'));
        $name = $request->get('vmname');

        /** Get box options from config */
        $boxes   = $this->get('config')['boxes'];
        $boxName = array_key_exists($request->get('baseBox'), $boxes) ? $request->get('baseBox') : 'precise64';
        $box     =  $boxes[$boxName];

        /** Get web server options from config */
        $webservers   = $this->get('config')['webservers'];
        $webServerKey = array_key_exists($request->get('webserver'), $webservers) ? $request->get('webserver') : 'nginxphp';
        $webserver    = $webservers[$webServerKey];

        /** Configure Vagrantfile */
        $vagrantfile = new VagrantfileRenderer();
        $vagrantfile->setName($name);
        $vagrantfile->setBoxName($boxName);
        $vagrantfile->setBoxUrl($box['url']);
        $vagrantfile->setMemory($request->get('memory'));
        $vagrantfile->setIpAddress($request->get('ipaddress'));
        $vagrantfile->setSyncedFolder($request->get('sharedfolder'));

        /** Configure Variable files - common */
        $common = new VarfileRenderer('common');
        $common->add('php_ppa', $request->get('phpppa'));
        $common->add('doc_root', $request->get('docroot'));
        $common->add('sys_packages', $request->get('syspackages', array()));
        $common->add('timezone', $request->get('timezone'));

        /** Configure Playbook */
        $playbook = new PlaybookRenderer();
        $playbook->addRole('init');

        $php_packages = $request->get('phppackages', array());

        /** Databases */
        if ($request->get('database-status')) {
            $playbook->addRole('mysql');

            $mysqlvars = new VarfileRenderer('mysql');
            $mysqlvars->setTemplate('roles/mysql.vars.twig');

            $mysqlvars->setData([ 'mysql_vars' => [
                    [
                        'user' => $request->get('user'),
                        'pass' => $request->get('password'),
                        'db'   => $request->get('database'),
                    ]
                ]]);

            $vagrant->addRenderer($mysqlvars);
            $playbook->addVarsFile('vars/mysql.yml');
            $php_packages[] = 'php5-mysql';
        }


        if ($request->get('xdebug')) {
            $php_packages[] = 'php5-xdebug';
        }

        $common->add('php_packages', array_unique($php_packages));

        foreach ($webserver['include'] as $role) {
            $playbook->addRole($role);
        }

        if ($request->get('composer')) {
            $playbook->addRole('composer');
        }


        $playbook->addRole('phpcommon');

        $tmpName = 'bundle_' . time();
        $zipPath = sys_get_temp_dir() . "/$tmpName.zip";

        $playbook->addVarsFile('vars/common.yml');

        $vagrant->addRenderer($playbook);
        $vagrant->addRenderer($common);
        $vagrant->addRenderer($vagrantfile);

        if ($vagrant->generateBundle($zipPath, $playbook->getRoles())) {

            $stream = function () use ($zipPath) {
                readfile($zipPath);
            };

            return $app->stream($stream, 200, array(
                'Content-length' => filesize($zipPath),
                'Content-Disposition' => 'attachment; filename="phansible_' . $name . '.zip"'
            ));

        } else {
            return new Response('An error occurred.');
        }

    }
}
