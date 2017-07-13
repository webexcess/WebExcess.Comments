<?php
namespace WebExcess\Comments\Service;

/*
 * This file is part of the WebExcess.Comments package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Service\LinkingService;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\UriBuilder as RealUriBuilder;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Http\Response;
use Neos\Flow\Mvc\Controller\Arguments;

/**
 * @Flow\Scope("singleton")
 */
class NodeUriBuilder
{

    /**
     * @var LinkingService
     * @Flow\Inject
     */
    protected $linkingService;

    /**
     * @var RealUriBuilder
     */
    protected $uriBuilder;

    /**
     * The injection of the faked UriBuilder is necessary to generate frontend URLs from the backend
     *
     * @param ConfigurationManager $configurationManager
     */
    public function injectUriBuilder(ConfigurationManager $configurationManager) {
        $_SERVER['FLOW_REWRITEURLS'] = 1;
        $httpRequest = Request::createFromEnvironment();
//        $httpRequest->setBaseUri(new Uri($this->baseDomain));
        $request = new ActionRequest($httpRequest);
        $uriBuilder = new RealUriBuilder();
        $uriBuilder->setRequest($request);
        $uriBuilder->setCreateAbsoluteUri(TRUE);
        $this->uriBuilder = $uriBuilder;
    }

    /**
     * Create the frontend URL to the node
     *
     * @param NodeInterface $node
     * @return string The URL of the node
     * @throws \Neos\Neos\Exception
     */
    public function getUriToNode(NodeInterface $node) {
        $uri = $this->linkingService->createNodeUri(
            new ControllerContext(
                $this->uriBuilder->getRequest(),
                new Response(),
                new Arguments(array()),
                $this->uriBuilder
            ),
            $node,
            $node->getContext()->getRootNode(),
            'html',
            TRUE
        );
        return $uri;
    }

}
