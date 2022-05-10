<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ModuleVersion
 * @package Punchout2Go\Punchout\Model
 */
class ModuleVersion
{
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * ModuleVersion constructor.
     * @param DeploymentConfig $deploymentConfig
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param Json $json
     * @param ReadFactory $readFactory
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ComponentRegistrarInterface $componentRegistrar,
        Json $json,
        ReadFactory $readFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->componentRegistrar = $componentRegistrar;
        $this->json = $json;
        $this->readFactory = $readFactory;
    }

    /**
     * Get module composer version
     *
     * @param $moduleName
     * @return \Magento\Framework\Phrase|string|void
     */
    public function getModuleVersion($moduleName)
    {
        $result = __('No version found');
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        try {
            $composerJsonData = $directoryRead->readFile('composer.json');
        } catch (\Exception $e) {
            return $result;
        }
        $data = $this->json->unserialize($composerJsonData);
        return !empty($data['version']) ? $data['version'] : $result;
    }
}
