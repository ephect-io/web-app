<?php

namespace Ephect\WebApp\Builder\Descriptors;

use Ephect\Forms\Components\ComponentEntity;
use Ephect\Forms\Components\ComponentInterface;
use Ephect\Forms\Components\Generators\ComponentParser;
use Ephect\Forms\Registry\CodeRegistry;
use Ephect\Forms\Registry\ComponentRegistry;
use Ephect\Framework\Modules\ModuleManifestReader;
use Ephect\Framework\Utils\File;
use Exception;

class ModuleDescriptor implements DescriptorInterface
{
    public function __construct(private string $modulePath)
    {
    }

    public function describe(string $sourceDir, string $filename): array
    {
        File::safeMkDir(COPY_DIR . pathinfo($filename, PATHINFO_DIRNAME));
        copy($sourceDir . $filename, COPY_DIR . $filename);

        //TODO: get module class from module middleware
        $reader = new ModuleManifestReader();
        $manifest = $reader->read($this->modulePath . DIRECTORY_SEPARATOR . REL_CONFIG_DIR);

        $moduleEntrypoint = $manifest->getEntrypoint();

        if ($moduleEntrypoint == null) {
            return [null, null];
        }

        if (!in_array(ComponentInterface::class, class_implements($moduleEntrypoint))) {
            throw new Exception("Module entry point must implement " . ComponentInterface::class . " or be null.");
        }

        return $this->parseComponent($moduleEntrypoint, $filename);

    }

    private function parseComponent(string $moduleEntrypointClass, string $filename): array
    {
        $comp = new $moduleEntrypointClass;
        $comp->load($filename);
        $comp->analyse();

        $uid = $comp->getUID();
        $parser = new ComponentParser($comp);
        $struct = $parser->doDeclaration($uid);
        $decl = $struct->toArray();

        CodeRegistry::write($comp->getFullyQualifiedFunction(), $decl);
        ComponentRegistry::write($filename, $uid);
        ComponentRegistry::write($comp->getUID(), $comp->getFullyQualifiedFunction());

        $entity = ComponentEntity::buildFromArray($struct->composition);
        $comp->add($entity);

        return [$comp->getFullyQualifiedFunction(), $comp];
    }
}