<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\UI\GDT_SearchField;
use GDO\Form\GDT_Form;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Core\GDT_Response;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_AntiCSRF;

final class Search extends MethodQueryList
{
	public function execute()
	{
		$tabs = Module_Forum::instance()->renderTabs();
		if (count($_POST)>0)
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
			$this->form = GDT_Form::make()->method('GET');
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

	#######################
	### MethodQueryList ###
	#######################
	public function gdoTable()
	{
		return GDO_ForumThread::table();
	}

	public function gdoQuery()
	{
		return $this->gdoTable()->select();
	}
	

	
}

