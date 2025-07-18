<?php
/**
 * Test script for the name generator
 * Run this to verify the name generation works correctly
 */

// Include WordPress environment
require_once('../../../../wp-load.php');

// Get the Angel Initiation instance
$angel_initiation = Angel_Initiation_Module::get_instance();

// Test name generation
echo "Testing Angel Name Generator\n";
echo "=============================\n\n";

// Generate 20 test names
echo "Generated names:\n";
for ($i = 1; $i <= 20; $i++) {
    // Use reflection to access private method
    $reflection = new ReflectionMethod($angel_initiation, 'generate_stoner_name');
    $reflection->setAccessible(true);
    $name = $reflection->invoke($angel_initiation);
    
    // Check if name contains 'r'
    $has_r = strpos(strtolower($name), 'r') !== false;
    $status = $has_r ? '✓' : '✗';
    
    echo sprintf("%2d. %-20s [%s]\n", $i, $name, $status);
}

// Show statistics
echo "\n\nName Format Examples:\n";
echo "- Single word format (e.g., HappyStoner)\n";
echo "- No spaces between emotion and word\n";
echo "- Must contain letter 'r' to be valid\n";