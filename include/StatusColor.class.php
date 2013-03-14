<?php
/**
 * Severity status colors according to Zabbix.
 */
class StatusColor
{
	public static $VALUES = array(
		'rgba(30, 245, 30, 0.4)',   // 0=green
		'rgba(41, 226, 248, 0.4)',  // 1=blue
		'rgba(255, 255, 60, 0.4)',  // 2=yellow
		'rgba(255, 147, 96, 0.45)', // 3=orange
		'rgba(255, 40, 40, 0.45)',  // 4=soft red
		'rgba(255, 0, 0, 0.65)'     // 5=FUBAR red
	);
}