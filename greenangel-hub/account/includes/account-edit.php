<?php
/**
 * 🌿 Green Angel - Account Details (Clean & Simple)
 * Just beautiful styling, no complications!
 */

// Add custom CSS to style the page beautifully
add_action('wp_head', 'greenangel_account_edit_styles_final');
function greenangel_account_edit_styles_final() {
    if (!is_wc_endpoint_url('edit-account')) return;
    ?>
    <style>
        /* Enhanced Green Angel Account Edit Styling */
        
        /* Add padding to the main account wrapper for smooth transitions */
        .woocommerce-MyAccount {
            padding: 2rem 0;
        }
        
        /* Container styling with proper padding */
        .woocommerce-MyAccount-content {
            background: linear-gradient(135deg, #1a1a1a 0%, #222222 100%);
            padding: 3rem 2.5rem;
            border-radius: 20px;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin: 2rem auto;
            max-width: 1200px;
        }
        
        /* Form container */
        .woocommerce-EditAccountForm {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(42, 42, 42, 0.5);
            padding: 2.5rem;
            border-radius: 16px;
            border: 1px solid #444;
        }
        
        /* Hide default heading */
        .woocommerce-MyAccount-content > h3 {
            display: none !important;
        }
        
        /* Add custom title inside form */
        .woocommerce-EditAccountForm:before {
            content: '👤 MY ACCOUNT DETAILS';
            display: block;
            background: #aed604;
            color: #222222;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1rem;
            text-align: center;
            margin: -0.5rem auto 2rem;
            width: fit-content;
            letter-spacing: 0.5px;
        }
        
        /* Form row styling */
        .woocommerce-form-row {
            margin-bottom: 1.75rem;
        }
        
        .woocommerce-form-row--wide {
            clear: both;
        }
        
        .woocommerce-form-row--first,
        .woocommerce-form-row--last {
            width: 48%;
            float: left;
        }
        
        .woocommerce-form-row--last {
            float: right;
        }
        
        /* Labels */
        .woocommerce-form-row label {
            color: #aed604;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Input fields */
        .woocommerce-form-row input[type="text"],
        .woocommerce-form-row input[type="email"],
        .woocommerce-form-row input[type="password"],
        .woocommerce-form-row select {
            width: 100%;
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid #333;
            color: #ffffff;
            padding: 0.875rem 1.25rem;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .woocommerce-form-row input:focus,
        .woocommerce-form-row select:focus {
            outline: none;
            border-color: #aed604;
            background: rgba(26, 26, 26, 0.9);
            box-shadow: 0 0 0 2px rgba(174, 214, 4, 0.1);
        }
        
        /* Placeholder text */
        .woocommerce-form-row input::placeholder {
            color: #666;
            opacity: 1;
        }
        
        /* Required asterisk */
        .required {
            color: #c6f731;
            font-weight: 700;
        }
        
        /* Field descriptions */
        .woocommerce-form-row span em {
            display: block;
            color: #999;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            font-style: normal;
            line-height: 1.4;
        }
        
        /* Password section */
        fieldset {
            border: none;
            padding: 2rem 0 0 0;
            margin: 2rem 0 0 0;
            border-top: 1px solid #333;
        }
        
        fieldset legend {
            color: #aed604;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 1rem 0 0;
        }
        
        /* Save button */
        .woocommerce-Button {
            background: linear-gradient(135deg, #aed604 0%, #c6f731 100%);
            color: #222222 !important;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(174, 214, 4, 0.2);
        }
        
        .woocommerce-Button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(174, 214, 4, 0.3);
        }
        
        .woocommerce-Button:active {
            transform: translateY(0);
        }
        
        /* Success/Error messages */
        .woocommerce-message,
        .woocommerce-error {
            background: rgba(174, 214, 4, 0.1);
            border: 1px solid rgba(174, 214, 4, 0.3);
            color: #aed604;
            padding: 1.25rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 500;
            list-style: none;
        }
        
        .woocommerce-error {
            background: rgba(255, 107, 107, 0.1);
            border-color: rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }
        
        .woocommerce-error li {
            margin: 0;
        }
        
        /* Clear floats */
        .clear {
            clear: both;
        }
        
        /* Hide navigation sidebar */
        .woocommerce-MyAccount-navigation {
            display: none;
        }
        
        .woocommerce-MyAccount-content {
            width: 100%;
            float: none;
        }
        
        /* Add bottom padding to prevent harsh cutoff */
        body.woocommerce-account {
            padding-bottom: 4rem;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .woocommerce-MyAccount {
                padding: 1rem 0;
            }
            
            .woocommerce-MyAccount-content {
                padding: 2rem 1.5rem;
                border-radius: 16px;
                margin: 1rem;
            }
            
            .woocommerce-EditAccountForm {
                padding: 1.5rem;
            }
            
            .woocommerce-form-row--first,
            .woocommerce-form-row--last {
                width: 100%;
                float: none;
            }
            
            body.woocommerce-account {
                padding-bottom: 3rem;
            }
        }
        
        @media (max-width: 480px) {
            .woocommerce-MyAccount-content {
                padding: 1.5rem 1rem;
                margin: 0.5rem;
            }
            
            .woocommerce-EditAccountForm {
                padding: 1.25rem;
            }
            
            body.woocommerce-account {
                padding-bottom: 2rem;
            }
        }
    </style>
    <?php
}