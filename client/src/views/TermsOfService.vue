<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-6 py-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <svg width="40" height="40" viewBox="0 0 64 64" class="flex-shrink-0">
              <defs>
                <linearGradient id="hexGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                  <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                </linearGradient>
              </defs>
              <polygon points="32,4 56,18 56,46 32,60 8,46 8,18" fill="url(#hexGrad)" stroke="#7c3aed" stroke-width="1.5"/>
              <circle cx="32" cy="32" r="10" fill="#ec4899"/>
            </svg>
            <div>
              <h2 class="text-gray-900 font-bold text-lg">TaskFlow</h2>
              <p class="text-gray-500 text-xs">Distributed Task Scheduler</p>
            </div>
          </div>
          <router-link to="/register" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
            ← Back to Register
          </router-link>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-6 py-12">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Service</h1>
        <p class="text-sm text-gray-500 mb-8">Last Updated: May 28, 2026</p>

        <div class="prose prose-indigo max-w-none">
          <!-- Introduction -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              Welcome to TaskFlow Distributed Task Scheduler ("TaskFlow", "we", "us", or "our"). These Terms of Service ("Terms") govern your access to and use of the TaskFlow platform, including our website, applications, and services (collectively, the "Service").
            </p>
            <p class="text-gray-700 leading-relaxed">
              By accessing or using our Service, you agree to be bound by these Terms. If you do not agree to these Terms, please do not use our Service.
            </p>
          </section>

          <!-- Account Registration -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Account Registration</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              To use TaskFlow, you must create an account. You agree to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Provide accurate, current, and complete information during registration</li>
              <li>Maintain and promptly update your account information</li>
              <li>Maintain the security of your password and account</li>
              <li>Accept responsibility for all activities that occur under your account</li>
              <li>Notify us immediately of any unauthorized use of your account</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              You must be at least 18 years old to create an account. By creating an account, you represent that you meet this age requirement.
            </p>
          </section>

          <!-- Acceptable Use -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Acceptable Use</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              You agree to use TaskFlow only for lawful purposes. You agree NOT to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Violate any applicable laws or regulations</li>
              <li>Infringe upon the rights of others</li>
              <li>Submit malicious code, viruses, or harmful content</li>
              <li>Attempt to gain unauthorized access to our systems</li>
              <li>Interfere with or disrupt the Service or servers</li>
              <li>Use the Service to distribute spam or unsolicited messages</li>
              <li>Engage in any activity that could harm TaskFlow or its users</li>
              <li>Use the Service for cryptocurrency mining without authorization</li>
              <li>Overload the system with excessive job submissions</li>
              <li>Reverse engineer or attempt to extract source code</li>
            </ul>
          </section>

          <!-- Service Description -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Service Description</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              TaskFlow provides a distributed task scheduling platform that allows users to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Submit computational jobs for processing</li>
              <li>Monitor job progress and status in real-time</li>
              <li>Manage worker nodes for task execution</li>
              <li>Access system analytics and performance metrics</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              We reserve the right to modify, suspend, or discontinue any part of the Service at any time with or without notice.
            </p>
          </section>

          <!-- User Content -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. User Content and Data</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              You retain ownership of any content, data, or jobs you submit to TaskFlow ("User Content"). By submitting User Content, you grant us a limited license to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Store and process your User Content to provide the Service</li>
              <li>Create backups for data protection purposes</li>
              <li>Display job status and results to you</li>
            </ul>
            <p class="text-gray-700 leading-relaxed mb-4">
              You are responsible for:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Ensuring you have the right to submit your User Content</li>
              <li>Maintaining backups of your important data</li>
              <li>The accuracy and legality of your User Content</li>
              <li>Any consequences resulting from your User Content</li>
            </ul>
          </section>

          <!-- Privacy and Security -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Privacy and Security</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              Your privacy is important to us. Our collection and use of personal information is described in our <router-link to="/privacy-policy" class="text-indigo-600 hover:text-indigo-700 font-medium">Privacy Policy</router-link>.
            </p>
            <p class="text-gray-700 leading-relaxed">
              While we implement security measures to protect your data, no system is completely secure. You acknowledge that you use the Service at your own risk.
            </p>
          </section>

          <!-- Fees and Payment -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Fees and Payment</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              TaskFlow may offer both free and paid service tiers. If you subscribe to a paid plan:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>You agree to pay all applicable fees</li>
              <li>Fees are non-refundable except as required by law</li>
              <li>We may change fees with 30 days' notice</li>
              <li>You authorize us to charge your payment method</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              We reserve the right to suspend or terminate accounts with unpaid fees.
            </p>
          </section>

          <!-- Intellectual Property -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Intellectual Property</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              The Service, including its design, code, features, and content (excluding User Content), is owned by TaskFlow and protected by copyright, trademark, and other intellectual property laws.
            </p>
            <p class="text-gray-700 leading-relaxed">
              You may not copy, modify, distribute, sell, or lease any part of our Service without our express written permission.
            </p>
          </section>

          <!-- Termination -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Termination</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We may terminate or suspend your account and access to the Service:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>If you violate these Terms</li>
              <li>If we suspect fraudulent or illegal activity</li>
              <li>For any reason with 30 days' notice</li>
              <li>Immediately for serious violations</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              You may terminate your account at any time through your account settings. Upon termination, your right to use the Service will immediately cease.
            </p>
          </section>

          <!-- Disclaimers -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Disclaimers</h2>
            <p class="text-gray-700 leading-relaxed mb-4 uppercase font-semibold">
              THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND.
            </p>
            <p class="text-gray-700 leading-relaxed mb-4">
              We disclaim all warranties, express or implied, including:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Warranties of merchantability and fitness for a particular purpose</li>
              <li>Warranties regarding accuracy, reliability, or availability</li>
              <li>Warranties that the Service will be uninterrupted or error-free</li>
              <li>Warranties regarding the security of your data</li>
            </ul>
          </section>

          <!-- Limitation of Liability -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Limitation of Liability</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              TO THE MAXIMUM EXTENT PERMITTED BY LAW, TASKFLOW SHALL NOT BE LIABLE FOR:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Indirect, incidental, special, or consequential damages</li>
              <li>Loss of profits, data, or business opportunities</li>
              <li>Damages resulting from your use or inability to use the Service</li>
              <li>Damages resulting from unauthorized access to your data</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              Our total liability shall not exceed the amount you paid us in the 12 months preceding the claim, or $100, whichever is greater.
            </p>
          </section>

          <!-- Indemnification -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Indemnification</h2>
            <p class="text-gray-700 leading-relaxed">
              You agree to indemnify and hold harmless TaskFlow, its affiliates, and their respective officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses (including legal fees) arising from:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mt-4">
              <li>Your use of the Service</li>
              <li>Your violation of these Terms</li>
              <li>Your User Content</li>
              <li>Your violation of any rights of another party</li>
            </ul>
          </section>

          <!-- Changes to Terms -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Changes to Terms</h2>
            <p class="text-gray-700 leading-relaxed">
              We may update these Terms from time to time. We will notify you of material changes by email or through the Service. Your continued use of the Service after changes become effective constitutes acceptance of the new Terms.
            </p>
          </section>

          <!-- Governing Law -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. Governing Law</h2>
            <p class="text-gray-700 leading-relaxed">
              These Terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law provisions. Any disputes shall be resolved in the courts of [Your Jurisdiction].
            </p>
          </section>

          <!-- Contact -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">15. Contact Information</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              If you have questions about these Terms, please contact us at:
            </p>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700"><strong>Email:</strong> legal@taskflow.com</p>
              <p class="text-gray-700"><strong>Address:</strong> TaskFlow Legal Department</p>
              <p class="text-gray-700 ml-16">[Your Address]</p>
            </div>
          </section>

          <!-- Acceptance -->
          <section class="mb-8">
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
              <h3 class="text-lg font-semibold text-indigo-900 mb-2">Acceptance of Terms</h3>
              <p class="text-indigo-800">
                By clicking "Create Account" or using our Service, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
              </p>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// No script needed for this static page
</script>

<style scoped>
.prose h2 {
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.prose p {
  margin-bottom: 1rem;
}

.prose ul {
  margin-bottom: 1rem;
}
</style>
