<?php
namespace GDO\Forum\Method;

use GDO\Table\GDT_List;
use GDO\Table\MethodQueryList;
use GDO\UI\GDT_SearchField;
use GDO\Form\GDT_Form;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Core\GDO;
use GDO\Core\GDT_Response;
use GDO\Form\GDT_Submit;
use GDO\User\GDO_User;

/**
 * Forum search.
 * Does not use templates yet.
 * @author gizmore
 * @version 6.07
 */
final class Search extends MethodQueryList
{
	public function execute()
	{
		$tabs = Module_Forum::instance()->renderTabs();
		if (isset($_REQUEST['submit']))
		{
			return $tabs->add($this->renderForm())->add(parent::execute());
		}
		return $tabs->add($this->renderForm());
	}

	###################
	### Search Form ###
	###################
	private $form;
	public function formSearch()
	{
		if (!$this->form)
		{
			$this->form = GDT_Form::make('form')->method('GET');
			$this->form->addFields(array(
				GDT_SearchField::make('search'),
				GDT_Submit::make(),
			));
		}
		return $this->form;
	}
	
	public function renderForm()
	{
		return GDT_Response::makeWith($this->formSearch());
	}

	private function searchTerm()
	{
		return (string)@$_REQUEST['form']['search'];
	}
	
	#######################
	### MethodQueryList ###
	#######################
	public function gdoTable()
	{
		return GDO_ForumThread::table();
	}

	public function gdoDecorateList(GDT_List $list)
	{
		$list->title(t('list_forum_search', [html($this->searchTerm())]));
	}
	
	public function getQuery()
	{
		$term = GDO::escapeS($this->searchTerm());
		$term = "'%$term%'";
		$level = GDO_User::current()->getLevel();
		$subselect = "(SELECT COUNT(*) FROM gdo_forumpost WHERE $level >= post_level AND post_thread=thread_id AND post_message LIKE $term)";
		$query = $this->gdoTable()->select()->where("thread_title LIKE $term")->where($subselect, 'OR');
		return $query;
	}
}
