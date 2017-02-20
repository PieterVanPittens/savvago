<?php
/**
 * Progress Types
 * enum
 */
abstract class ProgressTypes {
	const FinishedSectionsTotal = 1;
	const FinishedLesson = 2;
	/**
	 * number of lessons finished in course
	 */
	const CourseFinishedLessonsTotal = 3;
	/**
	 * number of lessons finished in section
	 */
	const SectionFinishedLessonsTotal = 4;
	/**
	 * enrolled to course
	 */
	const Enrolled = 5;
}
?>