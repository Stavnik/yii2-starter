<?php

namespace justcoded\yii2\rbac\forms;

use justcoded\yii2\rbac\models\Item;
use yii\helpers\ArrayHelper;
use yii\rbac\Role;
use Yii;


class RoleForm extends ItemForm
{

	public $allow_permissions;
	public $deny_permissions;
	public $inherit_permissions;
	public $role;
	public $permissions;
	public $permissions_search;


	/**
	 * @inheritdoc
	 * @return array
	 */
	public function rules()
	{
		return  ArrayHelper::merge(parent::rules(),[
			[['allow_permissions', 'deny_permissions', 'permissions', 'inherit_permissions'], 'safe']
		]);
	}


	/**
	 * @param $attribute
	 * @return bool
	 */
	public function uniqueName($attribute)
	{

		if (Yii::$app->authManager->getRole($this->attributes['name'])) {
			$this->addError($attribute, 'Name must be unique');

			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function beforeValidate()
	{
		$this->type = Role::TYPE_ROLE;
		$this->permissions = explode(',', $this->allow_permissions);
		return parent::beforeValidate();
	}


	/**
	 * @return array|bool
	 */
	public function getInheritPermissions()
	{
		if(empty($this->name)){
			return false;
		}

		$child = Yii::$app->authManager->getChildRoles($this->name);
		ArrayHelper::remove($child, $this->name);

		return ArrayHelper::map($child, 'name', 'name');
	}

	/**
	 * @return bool|null|string
	 */
	public function getAllowPermissions()
	{

		if ($this->name){
			$permissions = Yii::$app->authManager->getPermissionsByRole($this->name);
		}else{
			$permissions = Yii::$app->authManager->getPermissions();
		}

		if (empty($permissions) || !is_array($permissions)){
			return false;
		}

		$permissions_name = implode(',', array_keys($permissions));


		return $permissions_name;
	}

	#TODO Creating tree
	public function arrayAllowPermissions()
	{

		$permissions = Yii::$app->authManager->getPermissions();

		if(!$permissions){
			return false;
		}
		//ArrayHelper::remove($permissions, '*');
		//pa($permissions['admin/dashboard/*']);

		//$permissions = array_keys($permissions_a);

		$html = '<ul>';

		foreach ($permissions as $name => $permission) {
			$html .= '<li>'. $name;
			$child = Yii::$app->authManager->getChildren($name);
			if (!empty($child)){
				$child = array_keys($child);
				$html .= '<ul>';
				foreach ($child as $name_child) {
					ArrayHelper::remove($permissions, $name_child);
					$html .= "<li>$name_child</li>";
				}
				$html .= '</ul>';
			}
			$html .= '</li>';
		}
		$html .= '</ul>';

//		$array = [];
//		$html = '<ul>';
//		foreach ($permissions as $key => $permission){
//			$html .= '<li>'. $key;
//			if(!empty($permission->data)){
//				$html .= '<ul>';
//				foreach ($permission->data as $name => $child) {
//					$html .= '<li>' . $name .'</li>';
//				}
//				$html .= '</ul>';
//			}
//			$html .= '</li>';
//		}
//		$html .= '</ul>';

		return $html;
	}

	public function createTree($name, $html)
	{
		$html .= '<li>'. $name;
		$child = Yii::$app->authManager->getChildren($name);
		if (!empty($child)){
			$child = array_keys($child);
			$html .= '<ul>';
			foreach ($child as $name) {
				$html .= $this->createTree($name, $html);
			}
			$html .= '</ul>';
		}
		$html .= '</li>';

		return $html;
	}


	/**
	 * @return mixed
	 */
	public function getListInheritPermissions()
	{
		$roles = $this->rolesList;
		ArrayHelper::remove($roles, $this->name);

		return $roles;
	}
}
