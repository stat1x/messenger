<?php
namespace Fab\Messenger\ContentRenderer;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Messenger\Domain\Model\MessageTemplate;

/**
 * This class is for rendering content in the context of the Frontend.
 */
class FrontendRenderer implements ContentRendererInterface
{

    /**
     * @var MessageTemplate
     */
    protected $messageTemplate;

    /**
     * Constructor
     *
     * @param MessageTemplate $messageTemplate
     */
    public function __construct(MessageTemplate $messageTemplate = null)
    {
        $this->messageTemplate = $messageTemplate;
    }

    /**
     * Render content in the context of the Frontend.
     *
     * @param string $content
     * @param array $markers
     * @return string
     */
    public function render($content, array $markers): string
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->getObjectManager()->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setTemplateSource($content);

        // If a template file was defined, set its path, so that layouts and partials can be used
        // NOTE: they have to be located in sub-folders called "Layouts" and "Partials" relative
        // to the folder where the template is stored.
        if ($this->messageTemplate && $this->messageTemplate->getSourceFile()) {
            $sourceFile = $this->messageTemplate->getSourceFile();
            if (!empty($sourceFile)) {
                $sourceFileNameAndPath = GeneralUtility::getFileAbsFileName($sourceFile);
                $view->setTemplatePathAndFilename($sourceFileNameAndPath);
            }
        }

        $view->assignMultiple($markers);
        return trim($view->render());
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager|object
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    }
}
