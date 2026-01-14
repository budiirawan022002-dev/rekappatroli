<?php
// Header file that contains the top HTML, head section, and opening body tag
?>
<!doctype html>
<html lang="en" data-bs-theme="bordered-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rekap Hastag | Upload dan Proses Laporan</title>
  <!--favicon-->
  <link rel="icon" href="template_web/assets/images/favicon-32x32.png" type="image/png">
  <!-- loader-->
  <link href="template_web/assets/css/pace.min.css" rel="stylesheet">
  <script src="template_web/assets/js/pace.min.js"></script>

  <!--plugins-->
  <link href="template_web/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="template_web/assets/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="template_web/assets/plugins/metismenu/mm-vertical.css">
  <link rel="stylesheet" type="text/css" href="template_web/assets/plugins/simplebar/css/simplebar.css">
  <!--bootstrap css-->
  <link href="template_web/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <!--main css-->
  <link href="template_web/assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="template_web/sass/main.css" rel="stylesheet">
  <link href="template_web/assets/css/horizontal-menu.css" rel="stylesheet">
  <link href="template_web/sass/dark-theme.css" rel="stylesheet">
  <link href="template_web/sass/blue-theme.css" rel="stylesheet">
  <link href="template_web/sass/semi-dark.css" rel="stylesheet">
  <link href="template_web/sass/bordered-theme.css" rel="stylesheet">
  <link href="template_web/sass/responsive.css" rel="stylesheet">
  <!-- Custom CSS for app -->
  <link href="node_modules/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/progress-enhanced.css" rel="stylesheet">
  <!-- <link href="css/custom.css" rel="stylesheet"> -->
  <style>
    .wizard-step {
      transition: all 0.3s ease;
    }

    .card-header {
      padding: 0.75rem 1rem;
    }

    #progressOverlay {
      z-index: 9999;
    }

    /* Premium Checkbox Design - Clear and Sharp */
    .form-check {
      padding: 12px 15px;
      margin-bottom: 10px;
      border-radius: 12px;
      background: #ffffff;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .form-check:hover {
      background: #e7f1ff;
      border-color: #0d6efd;
      transform: translateX(5px);
      box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }
    
    .form-check-input {
      width: 20px;
      height: 20px;
      margin-top: 0.25rem;
      border: 2px solid #dee2e6;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .form-check-input:checked {
      background: #0d6efd;
      border-color: #0d6efd;
      box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
      transform: scale(1.1);
    }
    
    .form-check-input:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .form-check-label {
      font-weight: 500;
      color: #212529;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 15px;
    }
    
    .form-check:hover .form-check-label {
      color: #0d6efd;
    }
    
    .form-check-input:checked ~ .form-check-label {
      color: #212529;
      font-weight: 600;
    }
    
    .form-check-label i {
      font-size: 18px;
      transition: all 0.3s ease;
    }
    
    .form-check:hover .form-check-label i {
      transform: scale(1.2);
      color: #0d6efd;
    }

    /* Dropdown menu styles - Premium Design */
    .dropdown-menu {
      min-width: 250px;
      border: none;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
      border-radius: 12px;
      padding: 8px;
      margin-top: 8px !important;
      animation: fadeInDown 0.3s ease;
      z-index: 1050 !important;
    }
    
    .dropdown-menu.show {
      display: block !important;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 4px;
      transition: all 0.2s ease;
    }
    
    .dropdown-item:hover,
    .dropdown-item:focus {
      background: #e7f1ff;
      color: #0d6efd;
      transform: translateX(3px);
    }
    
    .dropdown-item.active {
      background: #0d6efd;
      color: white;
    }

    .dropdown-item .parent-icon {
      display: flex;
      align-items: center;
      margin-right: 10px;
    }

    .dropdown-item .menu-title {
      flex: 1;
      white-space: normal;
      word-wrap: break-word;
      min-width: 0;
      line-height: 1.3;
    }

    /* Result display styles - Editable and Scrollable */
    .result-textarea {
      font-size: 13px !important;
      resize: both !important;
      min-height: 150px !important;
      max-height: 600px !important;
      height: auto !important;
      overflow-y: auto !important;
      overflow-x: auto !important;
      width: 100% !important;
      font-family: 'Courier New', monospace !important;
      line-height: 1.6 !important;
      padding: 12px !important;
      border: 2px solid #dee2e6 !important;
      border-radius: 8px !important;
      background: #ffffff !important;
      cursor: text !important;
      white-space: pre-wrap !important;
      word-wrap: break-word !important;
    }
    
    .result-textarea:focus {
      outline: none !important;
      border-color: #0d6efd !important;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
      background: #ffffff !important;
    }
    
    .result-textarea:hover {
      border-color: #0d6efd !important;
    }
    
    /* Ensure textarea is not readonly */
    .result-textarea[readonly] {
      cursor: text !important;
      background: #ffffff !important;
    }
    
    /* Remove readonly attribute via CSS if needed */
    textarea.result-textarea {
      -webkit-user-select: text !important;
      -moz-user-select: text !important;
      -ms-user-select: text !important;
      user-select: text !important;
    }

    .result-file-item {
      padding: 8px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 6px;
    }

    .result-file-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }

    .result-file-name {
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 5px;
      word-break: break-word;
    }

    .result-btn-group {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .result-btn {
      font-size: 12px;
      padding: 4px 8px;
      flex: 1;
      min-width: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .result-btn i {
      font-size: 14px;
      vertical-align: text-bottom;
    }
    
    /* Enhanced Card Styles - Premium Design */
    .card {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }
    
    .card:hover {
      transform: translateY(-5px) scale(1.01);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    .card-header {
      font-weight: 600;
      position: relative;
      overflow: hidden;
    }
    
    .card-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .card:hover .card-header::before {
      left: 100%;
    }
    
    /* Premium Result Cards - Blue Theme */
    .card.rounded-4 {
      background: #ffffff;
      border: 1px solid #cfe2ff;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }
    
    .card.rounded-4:hover {
      box-shadow: 0 15px 50px rgba(13, 110, 253, 0.15);
      border-color: #86b7fe;
    }
    
    /* Enhanced Card Headers with Blue Theme */
    .card-header.bg-primary {
      background: #0d6efd !important;
      box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }
    
    .card-header.bg-success {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
      box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
    
    .card-header.bg-danger {
      background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%) !important;
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }
    
    .card-header.bg-warning {
      background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
      box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }
    
    .card-header.bg-info {
      background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%) !important;
      box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
    }
    
    /* Enhanced Form Styles - Premium Design */
    .form-control, .form-select {
      border: 2px solid #dee2e6;
      border-radius: 10px;
      padding: 10px 15px;
      transition: all 0.3s ease;
      background: #ffffff;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.3rem rgba(13, 110, 253, 0.2);
      background: #ffffff;
      transform: translateY(-1px);
    }
    
    .form-control:hover, .form-select:hover {
      border-color: #0d6efd;
    }
    
    /* Premium Button Styles - Blue Theme */
    .btn-primary {
      background: #0d6efd;
      border: none;
      border-radius: 12px;
      padding: 12px 30px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .btn-primary::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    .btn-primary:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn-primary:hover {
      background: #0b5ed7;
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
    }
    
    .btn-primary:active {
      transform: translateY(-1px) scale(1.02);
    }
    
    .btn-secondary {
      border-radius: 12px;
      padding: 12px 30px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: 2px solid #6c757d;
    }
    
    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }
    
    /* Enhanced Menu Styles */
    .nav-link {
      transition: all 0.2s ease;
    }
    
    .nav-link:hover {
      color: #0d6efd !important;
    }
    
    /* Premium Step Indicator - Clear and Sharp */
    .wizard-step {
      background: #ffffff;
      border-radius: 16px;
      padding: 25px;
      border: 1px solid #cfe2ff;
      box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
    }
    
    .wizard-step h5 {
      color: #212529;
      font-weight: 700;
      font-size: 1.3rem;
      margin-bottom: 15px;
    }
    
    .wizard-step p.text-secondary,
    .wizard-step p {
      color: #495057;
      font-size: 0.95rem;
      line-height: 1.6;
      font-weight: 400;
    }
    
    /* Premium Label Styles - Clear and Sharp */
    .form-label {
      font-weight: 600;
      color: #212529;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 15px;
    }
    
    .form-label i {
      color: #0d6efd;
      font-size: 20px;
    }
    
    /* Premium Result Cards */
    .result-card {
      border-left: 4px solid transparent;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .result-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, transparent, currentColor, transparent);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .result-card:hover::after {
      opacity: 0.3;
    }
    
    .result-card.bg-primary {
      border-left-color: #0d6efd;
    }
    
    .result-card.bg-success {
      border-left-color: #28a745;
    }
    
    .result-card.bg-warning {
      border-left-color: #ffc107;
    }
    
    .result-card.bg-danger {
      border-left-color: #dc3545;
    }
    
    /* Premium Result Placeholder */
    .result-placeholder {
      transition: all 0.3s ease;
    }
    
    .result-icon-large {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card:hover .result-icon-large {
      transform: scale(1.15) rotate(5deg);
      box-shadow: 0 8px 25px rgba(13, 110, 253, 0.2);
    }
    
    .result-placeholder p {
      color: #adb5bd;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }
    
    .card:hover .result-placeholder p {
      color: #6c757d;
    }
    
    /* Premium Result Card */
    .result-card-premium {
      position: relative;
      overflow: hidden;
    }
    
    .result-card-premium::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(13, 110, 253, 0.05) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.5s ease;
    }
    
    .result-card-premium:hover::before {
      opacity: 1;
    }
    
    .result-icon-wrapper {
      transition: all 0.3s ease;
    }
    
    .card:hover .result-icon-wrapper {
      transform: scale(1.1) rotate(-5deg);
      background: rgba(255, 255, 255, 0.3) !important;
    }
    
    /* Step Number Circle Animation */
    .step-number-circle {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }
    
    .wizard-step:hover .step-number-circle {
      transform: scale(1.1) rotate(360deg);
      box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
    }
    
    /* Premium File Input */
    .form-control[type="file"] {
      padding: 8px;
      border: 2px dashed #dee2e6;
      background: #f8f9fa;
      transition: all 0.3s ease;
    }
    
    .form-control[type="file"]:hover {
      border-color: #0d6efd;
      background: #e7f1ff;
    }
    
    .form-control[type="file"]:focus {
      border-color: #0d6efd;
      border-style: solid;
      background: #cfe2ff;
    }
    
    /* Premium Textarea */
    textarea.form-control {
      border-radius: 12px;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
      min-height: 120px;
    }
    
    textarea.form-control:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.3rem rgba(13, 110, 253, 0.2);
    }
    
    /* Premium Alert Styles */
    .alert {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    
    .alert-info {
      background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(0, 123, 255, 0.1) 100%);
      border-left: 4px solid #0dcaf0;
    }
    
    .alert-warning {
      background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%);
      border-left: 4px solid #ffc107;
    }
    
    .alert-success {
      background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
      border-left: 4px solid #28a745;
    }
    
    /* Premium Background Effects */
    .main-wrapper {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      position: relative;
    }
    
    .main-wrapper::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 50%, rgba(13, 110, 253, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(13, 110, 253, 0.03) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }
    
    .main-content {
      position: relative;
      z-index: 1;
    }
    
    /* Premium Card Body */
    .card-body {
      background: rgba(255, 255, 255, 0.98);
    }
    
    /* Premium Input Groups */
    .input-group {
      border-radius: 12px;
      overflow: hidden;
    }
    
    .input-group .form-control {
      border-radius: 12px 0 0 12px;
    }
    
    .input-group .btn {
      border-radius: 0 12px 12px 0;
    }
    
    /* Premium Badge Styles */
    .badge {
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
    }
    
    /* Premium Loading Spinner */
    .loading-spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: #ffffff;
      animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Premium Scrollbar */
    ::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
    
    /* Premium Focus States */
    *:focus {
      outline: none;
    }
    
    *:focus-visible {
      outline: 2px solid #0d6efd;
      outline-offset: 2px;
      border-radius: 4px;
    }
    
    /* Premium Transitions */
    * {
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }
    
    /* Premium Shadow Utilities */
    .shadow-premium {
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1) !important;
    }
    
    .shadow-premium-lg {
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Premium Gradient Text */
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Premium Icon Animations */
    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.1);
      }
    }
    
    .card:hover .result-icon-wrapper i {
      animation: pulse 1s ease-in-out infinite;
    }
    
    /* Premium Breadcrumb Enhancement */
    .page-breadcrumb {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.95) 100%);
      padding: 18px 25px;
      border-radius: 16px;
      margin-bottom: 25px;
      border: 1px solid #cfe2ff;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      backdrop-filter: blur(10px);
    }
    
    .page-breadcrumb h4 {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .breadcrumb-item a {
      transition: all 0.3s ease;
    }
    
    .breadcrumb-item a:hover {
      color: #0d6efd !important;
      transform: translateX(3px);
    }
    
    /* Enhanced Dropdown Styles */
    .dropdown-menu {
      animation: fadeInDown 0.3s ease;
      border: none !important;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .nav-link.dropdown-toggle:hover {
      background: rgba(102, 126, 234, 0.1) !important;
    }
    
    .dropdown-item-menu {
      display: block;
      padding: 10px 12px;
      border-radius: 8px;
      text-decoration: none;
      color: inherit;
      transition: all 0.2s ease;
      margin-bottom: 4px;
    }
    
    .dropdown-item-menu:hover {
      background: rgba(102, 126, 234, 0.08) !important;
      color: inherit;
      transform: translateX(3px);
    }
    
    .dropdown-item-menu:active {
      background: rgba(102, 126, 234, 0.15) !important;
    }
    
    .dropdown-item-menu.text-danger:hover {
      background: rgba(220, 53, 69, 0.1) !important;
    }
    
    .icon-wrapper {
      transition: all 0.2s ease;
    }
    
    .dropdown-item-menu:hover .icon-wrapper {
      background: rgba(102, 126, 234, 0.15) !important;
      transform: scale(1.05);
    }
    
    .dropdown-item-menu.text-danger:hover .icon-wrapper {
      background: rgba(220, 53, 69, 0.15) !important;
    }
    
    .user-avatar, .user-avatar-large {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    .dropdown-divider {
      margin: 8px 0;
      opacity: 0.2;
    }
    
    /* Main Wrapper Spacing - Reduced gap between header and content */
    .main-wrapper {
      margin-top: 10px !important;
      padding-top: 10px;
    }
    
    @media (min-width: 1280px) {
      .main-wrapper {
        margin-top: 10px !important;
        padding-top: 10px;
      }
    }
    
    /* Header Navigation Menu Styles */
    .header-nav-link {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px !important;
      border-radius: 8px;
      color: #495057;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid transparent;
      background: transparent;
      text-decoration: none;
      white-space: nowrap;
    }
    
    .header-nav-link .parent-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
      border-radius: 6px;
      background: #cfe2ff;
      color: #667eea;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      flex-shrink: 0;
    }
    
    .header-nav-link .parent-icon i {
      font-size: 18px;
    }
    
    .header-nav-link .menu-title {
      white-space: nowrap;
      font-size: 14px;
    }
    
    .header-nav-link:hover {
      background: #cfe2ff;
      color: #667eea;
    }
    
    .header-nav-link:hover .parent-icon {
      background: rgba(102, 126, 234, 0.18);
      color: #667eea;
      transform: scale(1.05);
    }
    
    .header-nav-link.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(102, 126, 234, 0.25);
      font-weight: 600;
    }
    
    .header-nav-link.active .parent-icon {
      background: rgba(255, 255, 255, 0.2);
      color: #ffffff;
    }
    
    .header-nav-link.active .dropy-icon {
      color: rgba(255, 255, 255, 0.9);
    }
    
    .header-nav-link .dropy-icon {
      margin-left: auto;
      transition: transform 0.2s ease;
      font-size: 18px;
      color: #6c757d;
    }
    
    .header-nav-link.dropdown-toggle[aria-expanded="true"] .dropy-icon {
      transform: rotate(180deg);
    }
    
    .header-nav-link.dropdown-toggle[aria-expanded="true"] {
      background: #cfe2ff;
      color: #667eea;
    }
    
    /* Usage Guide Accordion Enhanced Styles */
    .accordion-button:not(.collapsed) {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .accordion-button {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .accordion-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }
    
    .accordion-button:not(.collapsed) .bi-chevron-down {
      transform: rotate(180deg);
      transition: transform 0.3s ease;
    }
    
    .accordion-button .bi-chevron-down {
      transition: transform 0.3s ease;
    }
    
    .guide-icon-box {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .accordion-button:hover .guide-icon-box {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }
    
    .step-item {
      transition: all 0.3s ease;
    }
    
    .step-item:hover {
      transform: translateX(5px);
    }
    
    .step-number {
      transition: all 0.3s ease;
    }
    
    .step-item:hover .step-number {
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .code-block {
      transition: all 0.3s ease;
    }
    
    .code-block:hover {
      transform: translateX(3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .tip-item {
      transition: all 0.3s ease;
    }
    
    .tip-item:hover {
      transform: translateX(5px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1) !important;
    }
    
    .accordion-collapse {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Header Dropdown Menu Styles - Ensure it works */
    .top-header .dropdown-menu,
    .navbar-nav .dropdown-menu {
      border: 1px solid #e9ecef;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
      border-radius: 12px;
      padding: 8px;
      margin-top: 8px;
      min-width: 260px;
      animation: fadeInDown 0.25s ease;
      background: #ffffff;
      display: none;
      position: absolute;
      z-index: 1050 !important;
    }
    
    .top-header .dropdown-menu.show,
    .navbar-nav .dropdown-menu.show {
      display: block !important;
    }
    
    .top-header .dropdown-item,
    .navbar-nav .dropdown-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 16px;
      border-radius: 8px;
      color: #495057;
      transition: all 0.2s ease;
      margin-bottom: 3px;
      text-decoration: none;
    }
    
    .top-header .dropdown-item:last-child {
      margin-bottom: 0;
    }
    
    .top-header .dropdown-item.active {
      background: rgba(102, 126, 234, 0.1);
      color: #667eea;
      font-weight: 600;
    }
    
    .top-header .dropdown-item.active .parent-icon {
      background: rgba(102, 126, 234, 0.15);
      color: #667eea;
    }
    
    .top-header .dropdown-divider {
      margin: 8px 4px;
      opacity: 0.2;
      border-top: 1px solid #dee2e6;
    }
    
    .top-header .dropdown-item .parent-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      background: #cfe2ff;
      color: #667eea;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    
    .top-header .dropdown-item .parent-icon i {
      font-size: 16px;
    }
    
    .top-header .dropdown-item .menu-title {
      font-size: 14px;
    }
    
    .top-header .dropdown-item:hover,
    .top-header .dropdown-item.active {
      background: #cfe2ff;
      color: #667eea;
    }
    
    .top-header .dropdown-item:hover .parent-icon,
    .top-header .dropdown-item.active .parent-icon {
      background: rgba(102, 126, 234, 0.15);
      color: #667eea;
    }
    
    /* Mobile Offcanvas Menu Styles */
    .offcanvas {
      border: none;
      box-shadow: 4px 0 30px rgba(0, 0, 0, 0.15);
      width: 280px !important;
    }
    
    @media (min-width: 1200px) {
      .offcanvas {
        display: none !important;
      }
    }
    
    .offcanvas-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff;
      padding: 24px 20px;
      border-bottom: none;
    }
    
    .offcanvas-header .logo-text {
      color: #ffffff;
      font-weight: 700;
      margin: 0;
      font-size: 20px;
    }
    
    .offcanvas-header .btn-close {
      filter: invert(1);
      opacity: 0.9;
      font-size: 20px;
    }
    
    .offcanvas-header .btn-close:hover {
      opacity: 1;
    }
    
    .offcanvas-body {
      padding: 0;
      background: #f8f9fa;
    }
    
    .offcanvas-body .navbar-nav {
      flex-direction: column;
      padding: 12px;
      gap: 6px;
    }
    
    .offcanvas-body .nav-link {
      border-radius: 10px;
      margin-bottom: 4px;
      width: 100%;
      padding: 14px 16px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: #495057;
      font-weight: 500;
      transition: all 0.2s ease;
      background: #ffffff;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .offcanvas-body .nav-link:hover {
      background: #cfe2ff;
      color: #667eea;
      transform: translateX(4px);
    }
    
    .offcanvas-body .nav-link .parent-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      background: rgba(102, 126, 234, 0.1);
      color: #667eea;
      flex-shrink: 0;
    }
    
    .offcanvas-body .nav-link .parent-icon i {
      font-size: 20px;
    }
    
    .offcanvas-body .nav-link .menu-title {
      font-size: 15px;
      font-weight: 500;
    }
    
    .offcanvas-body .dropdown-menu {
      border: none;
      box-shadow: none;
      background: transparent;
      padding: 0;
      margin: 8px 0 0 52px;
    }
    
    .offcanvas-body .dropdown-item {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 4px;
      background: #ffffff;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .offcanvas-body .dropdown-item:hover {
      background: #cfe2ff;
      color: #667eea;
    }
    
    .offcanvas-body .dropdown-item .parent-icon {
      width: 36px;
      height: 36px;
    }
    
    .offcanvas-body .dropdown-divider {
      margin: 12px 0;
      opacity: 0.2;
    }
  </style>
</head>

<body>

  <!--start header-->
  <header class="top-header" style="background: #ffffff; border-bottom: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1030;">
    <nav class="navbar navbar-expand align-items-center justify-content-between container-xxl px-4" style="min-height: 70px; gap: 12px;">
      <div class="d-flex align-items-center gap-2 flex-grow-1">
        <!-- Mobile Toggle Button (Left) -->
        <button class="btn btn-link d-xl-none p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" style="border: none; color: #495057; margin-right: 4px;">
          <i class="material-icons-outlined" style="font-size: 28px;">menu</i>
        </button>
        
        <div class="logo-header d-flex align-items-center gap-2">
          <div class="logo-icon">
            <img src="template_web/assets/images/logo-icon.png" class="logo-img" width="45" alt="">
          </div>
          <div class="logo-name d-none d-sm-block">
            <h5 class="mb-0" style="font-size: 18px;">Rekap Hastag</h5>
          </div>
        </div>
        
        <!-- Navigation Menu (Desktop) -->
        <ul class="navbar-nav d-none d-xl-flex align-items-center gap-1" style="margin-left: 12px;">
          <?php 
          $currentPage = basename($_SERVER['PHP_SELF']);
          $isDashboard = ($currentPage === 'index.php' && !isset($_GET['page']));
          $isLaporan = in_array($currentPage, ['index.php', 'rekap_surya.php', 'rekap_danantara.php']);
          $isScreenshot = ($currentPage === 'screenshot_link.php');
          $isUserManagement = (strpos($currentPage, 'users.php') !== false);
          ?>
          <li class="nav-item">
            <a class="nav-link header-nav-link <?php echo $isDashboard ? 'active' : ''; ?>" href="index.php">
              <div class="parent-icon"><i class="bi bi-house-door-fill"></i></div>
              <div class="menu-title d-flex align-items-center">Dashboard</div>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link header-nav-link dropdown-toggle dropdown-toggle-nocaret <?php echo ($isLaporan && !$isDashboard) || $isScreenshot || $isUserManagement ? 'active' : ''; ?>" href="javascript:;" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="parent-icon"><i class="bi bi-file-earmark-text"></i></div>
              <div class="menu-title d-flex align-items-center">Laporan</div>
              <div class="ms-auto dropy-icon"><i class='material-icons-outlined'>expand_more</i></div>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
                  <div class="parent-icon"><i class="bi bi-shield-check"></i></div>
                  <div class="menu-title d-flex align-items-center">Upload Laporan</div>
                </a></li>
              <li><a class="dropdown-item <?php echo $currentPage === 'rekap_surya.php' ? 'active' : ''; ?>" href="rekap_surya.php">
                  <div class="parent-icon"><i class="bi bi-graph-up"></i></div>
                  <div class="menu-title d-flex align-items-center">Rekap Spreadsheet Surya</div>
                </a></li>
              <li><a class="dropdown-item <?php echo $currentPage === 'rekap_danantara.php' ? 'active' : ''; ?>" href="rekap_danantara.php">
                  <div class="parent-icon"><i class="bi bi-table"></i></div>
                  <div class="menu-title d-flex align-items-center">Rekap Spreadsheet Danantara</div>
                </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item <?php echo $isScreenshot ? 'active' : ''; ?>" href="screenshot_link.php">
                  <div class="parent-icon"><i class="bi bi-camera-fill"></i></div>
                  <div class="menu-title d-flex align-items-center">Screenshot Link</div>
                </a></li>
              <?php if (false && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><a class="dropdown-item <?php echo $isUserManagement ? 'active' : ''; ?>" href="admin/users.php">
                  <div class="parent-icon"><i class="bi bi-people"></i></div>
                  <div class="menu-title d-flex align-items-center">User Management</div>
                </a></li>
              <?php endif; ?>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link header-nav-link <?php echo ($currentPage === 'panduan.php') ? 'active' : ''; ?>" href="panduan.php">
              <div class="parent-icon"><i class="bi bi-book-fill"></i></div>
              <div class="menu-title d-flex align-items-center">Panduan</div>
            </a>
          </li>
        </ul>
      </div>

      <!-- Enhanced header navigation with dropdown -->
      <ul class="navbar-nav gap-2 nav-right-links align-items-center">
        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="javascript:;" data-bs-toggle="dropdown" style="padding: 6px 12px; border-radius: 8px;">
            <?php 
            $userName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
            $initials = strtoupper(substr($userName, 0, 1));
            ?>
            <div class="user-avatar" style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 16px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
              <?php echo $initials; ?>
            </div>
            <div class="d-none d-xl-flex flex-column align-items-start" style="line-height: 1.2;">
              <span class="fw-semibold" style="font-size: 14px; color: #333;">
                <?php echo htmlspecialchars($userName); ?>
              </span>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <span class="badge bg-danger" style="font-size: 10px; padding: 2px 6px;">Admin</span>
              <?php else: ?>
                <small class="text-muted" style="font-size: 11px;">User</small>
              <?php endif; ?>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 280px; margin-top: 8px; border-radius: 10px; padding: 12px;">
            <!-- User Info Header -->
            <li class="mb-3 pb-3 border-bottom">
              <div class="d-flex align-items-center gap-3">
                <div class="user-avatar-large" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 22px; flex-shrink: 0;">
                  <?php echo $initials; ?>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                  <div class="fw-semibold mb-1" style="font-size: 14px; word-wrap: break-word;">
                    <?php echo htmlspecialchars($userName); ?>
                  </div>
                  <small class="text-muted d-block mb-1" style="font-size: 12px; word-wrap: break-word;">
                    <?php echo htmlspecialchars($_SESSION['email'] ?? $_SESSION['username'] . '@example.com'); ?>
                  </small>
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <span class="badge bg-danger" style="font-size: 10px; padding: 3px 8px;">Administrator</span>
                  <?php endif; ?>
                </div>
              </div>
            </li>
            
            <!-- Menu Items -->
            <?php if (false && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li>
              <a class="dropdown-item-menu" href="admin/users.php">
                <div class="d-flex align-items-center gap-3">
                  <div class="icon-wrapper" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(102, 126, 234, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-people text-primary" style="font-size: 16px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-medium" style="font-size: 13px;">User Management</div>
                    <small class="text-muted" style="font-size: 11px;">Kelola pengguna sistem</small>
                  </div>
                </div>
              </a>
            </li>
            <?php endif; ?>
            
            <li>
              <a class="dropdown-item-menu" href="index.php">
                <div class="d-flex align-items-center gap-3">
                  <div class="icon-wrapper" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(102, 126, 234, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-speedometer2 text-primary" style="font-size: 16px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-medium" style="font-size: 13px;">Dashboard</div>
                    <small class="text-muted" style="font-size: 11px;">Halaman utama</small>
                  </div>
                </div>
              </a>
            </li>
            
            <li><hr class="dropdown-divider my-2" style="margin: 8px 0;"></li>
            
            <li>
              <a class="dropdown-item-menu text-danger" href="logout.php">
                <div class="d-flex align-items-center gap-3">
                  <div class="icon-wrapper" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(220, 53, 69, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-box-arrow-right text-danger" style="font-size: 16px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-medium" style="font-size: 13px;">Logout</div>
                    <small class="text-muted" style="font-size: 11px;">Keluar dari sistem</small>
                  </div>
                </div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>
  <!--end top header-->


  <!--navigation-->
  <!-- Primary menu hidden, navigation moved to top header -->
  
  <!-- Mobile Offcanvas Menu -->
  <div class="offcanvas offcanvas-start w-260" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
    <div class="offcanvas-header border-bottom h-70">
          <div class="d-flex align-items-center gap-2">
            <div class="">
              <img src="template_web/assets/images/logo-icon.png" class="logo-icon" width="45" alt="logo icon">
            </div>
            <div class="">
              <h4 class="logo-text">Rekap Hastag</h4>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    <div class="offcanvas-body p-0">
      <ul class="navbar-nav align-items-center flex-grow-1">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <div class="parent-icon"><i class="bi bi-house-door-fill"></i></div>
            <div class="menu-title d-flex align-items-center">Dashboard</div>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="parent-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="menu-title d-flex align-items-center">Laporan</div>
            <div class="ms-auto dropy-icon"><i class='material-icons-outlined'>expand_more</i></div>
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="index.php">
                <div class="parent-icon"><i class="bi bi-shield-check"></i></div>
                <div class="menu-title d-flex align-items-center">Upload Laporan</div>
              </a></li>
            <li><a class="dropdown-item" href="rekap_surya.php">
                <div class="parent-icon"><i class="bi bi-graph-up"></i></div>
                <div class="menu-title d-flex align-items-center">Rekap Spreadsheet Surya</div>
              </a></li>
            <li><a class="dropdown-item" href="rekap_danantara.php">
                <div class="parent-icon"><i class="bi bi-table"></i></div>
                <div class="menu-title d-flex align-items-center">Rekap Spreadsheet Danantara</div>
              </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="screenshot_link.php">
                <div class="parent-icon"><i class="bi bi-camera-fill"></i></div>
                <div class="menu-title d-flex align-items-center">Screenshot Link</div>
              </a></li>
            <?php if (false && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a class="dropdown-item" href="admin/users.php">
                <div class="parent-icon"><i class="bi bi-people"></i></div>
                <div class="menu-title d-flex align-items-center">User Management</div>
              </a></li>
            <?php endif; ?>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage === 'panduan.php') ? 'active' : ''; ?>" href="panduan.php">
            <div class="parent-icon"><i class="bi bi-book-fill"></i></div>
            <div class="menu-title d-flex align-items-center">Panduan</div>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <!--end navigation-->
  <!-- Enhanced Progress bar overlay -->
  <div id="progressOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(0,0,0,0.7);backdrop-filter:blur(3px);align-items:center;justify-content:center;">
    <div class="card rounded-4 shadow-lg" style="min-width:400px;max-width:90vw;padding:1.5rem;">
      <div class="card-body text-center">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-0 text-primary">Proses Laporan</h5>
          <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div id="progressBarStatus" class="text-primary mb-2 fs-5 fw-bold"></div>
        <div class="progress w-100 mb-3" style="height: 24px;">
          <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div>
        </div>
        
        <!-- Step indicators for processing stages -->
        <div class="d-flex justify-content-between mb-2 px-2">
          <div class="process-step text-center" data-step="1">
            <div class="step-indicator rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width:30px;height:30px;background:#ddd;color:#555;font-weight:bold;">1</div>
            <div class="step-label small">Persiapan</div>
          </div>
          <div class="process-step text-center" data-step="2">
            <div class="step-indicator rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width:30px;height:30px;background:#ddd;color:#555;font-weight:bold;">2</div>
            <div class="step-label small">Screenshot</div>
          </div>
          <div class="process-step text-center" data-step="3">
            <div class="step-indicator rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width:30px;height:30px;background:#ddd;color:#555;font-weight:bold;">3</div>
            <div class="step-label small">Dokumen</div>
          </div>
          <div class="process-step text-center" data-step="4">
            <div class="step-indicator rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width:30px;height:30px;background:#ddd;color:#555;font-weight:bold;">4</div>
            <div class="step-label small">Selesai</div>
          </div>
        </div>
        
        <div class="progress progress-thin mb-3" style="height:4px;">
          <div id="stepProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
        </div>
        
        <div id="progressBarServerMsg" class="alert alert-info py-2 mb-0 small text-start"></div>
        <button id="debugInfoToggle" class="btn btn-sm btn-outline-secondary mt-2 font-13" type="button">Show Debug Info</button>
        <div id="debugInfo" class="mt-2 p-2 bg-light rounded-3" style="display:none; width:100%; max-height:200px; overflow-y:auto; font-size:0.8rem; font-family:monospace; color:#212529;border:1px solid #dee2e6;"></div>
      </div>
    </div>
  </div>
