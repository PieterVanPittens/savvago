<?php

/**
 * User Types
 * enum
 */
abstract class UserTypes {
	/**
	 * student that is not registered yet
	 * @var integer
	 */
	const Anonymous = 1;
	/**
	 * Student
	 * @var integer
	 */
	const Student = 2;
	/**
	 * Teacher
	 * @var integer
	 */
	const Teacher = 3;
	/**
	 * Admin
	 * @var integer
	 */
	const Admin = 4;

}
?>
