<?php

/**
 * Navigation
 *
 * @author Jan Marek
 * @license MIT
 */

namespace Navigation;

use Nette\Application\UI\Control;

class Navigation extends Control
{

	/** @var NavigationNode */
	private $homepage;

	/** @var NavigationNode */
	private $current;

	/** @var bool */
	private $useHomepage = false;

	/** @var Nette\Callback */
	private $setupMenuTemplate;

	/** @var Nette\Callback */
	private $setupBreadcrumbsTemplate;

	/**
	 * Set node as current
	 * @param NavigationNode $node
	 */
	public function setCurrentNode(NavigationNode $node)
	{
		if (isset($this->current)) {
			$this->current->isCurrent = false;
		}
		$node->isCurrent = true;
		$this->current = $node;
	}

	/**
	 * Add navigation node as a child
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function add($label, $url)
	{
		return $this->getComponent('homepage')->add($label, $url);
	}

	/**
	 * Setup homepage
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function setupHomepage($label, $url)
	{
		$homepage = $this->getComponent('homepage');
		$homepage->label = $label;
		$homepage->url = $url;
		$this->useHomepage = true;
		return $homepage;
	}

	/**
	 * Homepage factory
	 * @param string $name
	 */
	protected function createComponentHomepage($name)
	{
		new NavigationNode($this, $name);
	}

	/**
	 * Render menu
	 * @param bool $renderChildren
	 * @param NavigationNode $base
	 * @param bool $renderHomepage
	 */
	public function renderMenu($renderChildren = TRUE, $base = NULL, $renderHomepage = TRUE)
	{
		$template = $this->createTemplate()
				->setFile(__DIR__ . '/menu.phtml');
		
		if (isset($this->setupMenuTemplate)) {
		    $template = $this->setupMenuTemplate->invoke($template);
		}
		
		$template->homepage = $base ? $base : $this->getComponent('homepage');
		$template->useHomepage = $this->useHomepage && $renderHomepage;
		$template->renderChildren = $renderChildren;
		$template->children = $this->getComponent('homepage')->getComponents();
		$template->render();
	}

	/**
	 * Render full menu
	 */
	public function render()
	{
		$this->renderMenu();
	}

	/**
	 * Render main menu
	 */
	public function renderMainMenu()
	{
		$this->renderMenu(FALSE);
	}

	/**
	 * Render breadcrumbs
	 */
	public function renderBreadcrumbs()
	{
		if (empty($this->current)) {
			return;
		}

		$items = array();
		$node = $this->current;

		while ($node instanceof NavigationNode) {
			$parent = $node->getParent();
			if (!$this->useHomepage && !($parent instanceof NavigationNode)) {
				break;
			}

			array_unshift($items, $node);
			$node = $parent;
		}

		$template = $this->createTemplate()
				->setFile(__DIR__ . '/breadcrumbs.phtml');
		
		if (isset($this->setupBreadcrumbsTemplate)) {
		    $template = $this->setupBreadcrumbsTemplate->invoke($template);
		}

		$template->items = $items;
		$template->render();
	}

	/**
	 * @param callable $callback($template)
	 */
	public function setupBreadcrumbsTemplate($callback)
	{
		$this->setupBreadcrumbsTemplate = callback($callback);
	}

	/**
	 * @param callable $callback($template)
	 */
	public function setupMenuTemplate($callback)
	{
		$this->setupMenuTemplate = callback($callback);
	}

	/**
	 * @return \Navigation\NavigationNode
	 */
	public function getCurrentNode()
	{
		return $this->current;
	}

}
