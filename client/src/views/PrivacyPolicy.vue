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
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Last Updated: May 28, 2026</p>

        <div class="prose prose-indigo max-w-none">
          <!-- Introduction -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              TaskFlow ("we", "us", or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our Distributed Task Scheduler platform.
            </p>
            <p class="text-gray-700 leading-relaxed">
              By using TaskFlow, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our Service.
            </p>
          </section>

          <!-- Information We Collect -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Information We Collect</h2>
            
            <h3 class="text-xl font-semibold text-gray-800 mb-3">2.1 Information You Provide</h3>
            <p class="text-gray-700 leading-relaxed mb-4">
              We collect information that you voluntarily provide when using our Service:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li><strong>Account Information:</strong> Name, email address, password (encrypted)</li>
              <li><strong>Profile Information:</strong> Optional profile details you choose to provide</li>
              <li><strong>Job Data:</strong> Tasks, jobs, and computational work you submit</li>
              <li><strong>Communications:</strong> Messages you send to our support team</li>
              <li><strong>Payment Information:</strong> Billing details (processed by third-party payment processors)</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">2.2 Automatically Collected Information</h3>
            <p class="text-gray-700 leading-relaxed mb-4">
              When you use TaskFlow, we automatically collect certain information:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li><strong>Usage Data:</strong> Job submissions, execution times, system interactions</li>
              <li><strong>Device Information:</strong> IP address, browser type, operating system</li>
              <li><strong>Log Data:</strong> Access times, pages viewed, errors encountered</li>
              <li><strong>Performance Data:</strong> System metrics, worker node statistics</li>
              <li><strong>Cookies:</strong> Session tokens and preferences (see Cookie Policy below)</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">2.3 Worker Node Information</h3>
            <p class="text-gray-700 leading-relaxed mb-4">
              If you operate worker nodes:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li>Worker node identifiers and API tokens</li>
              <li>System specifications and capabilities</li>
              <li>Task execution logs and performance metrics</li>
              <li>Heartbeat and health status data</li>
            </ul>
          </section>

          <!-- How We Use Your Information -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. How We Use Your Information</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We use the collected information for the following purposes:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2">
              <li><strong>Service Provision:</strong> To provide, maintain, and improve TaskFlow</li>
              <li><strong>Job Processing:</strong> To execute and manage your computational tasks</li>
              <li><strong>Account Management:</strong> To create and manage your account</li>
              <li><strong>Communication:</strong> To send service updates, notifications, and support responses</li>
              <li><strong>Security:</strong> To detect, prevent, and address fraud and security issues</li>
              <li><strong>Analytics:</strong> To understand usage patterns and improve our Service</li>
              <li><strong>Compliance:</strong> To comply with legal obligations</li>
              <li><strong>Billing:</strong> To process payments and manage subscriptions</li>
            </ul>
          </section>

          <!-- Data Sharing and Disclosure -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Sharing and Disclosure</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We do not sell your personal information. We may share your information in the following circumstances:
            </p>
            
            <h3 class="text-xl font-semibold text-gray-800 mb-3">4.1 Service Providers</h3>
            <p class="text-gray-700 leading-relaxed mb-4">
              We may share information with third-party service providers who perform services on our behalf:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Cloud hosting providers (for infrastructure)</li>
              <li>Payment processors (for billing)</li>
              <li>Email service providers (for communications)</li>
              <li>Analytics providers (for usage insights)</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">4.2 Legal Requirements</h3>
            <p class="text-gray-700 leading-relaxed mb-4">
              We may disclose your information if required by law or in response to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Legal processes (subpoenas, court orders)</li>
              <li>Government requests</li>
              <li>Protection of our rights and safety</li>
              <li>Investigation of potential violations</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">4.3 Business Transfers</h3>
            <p class="text-gray-700 leading-relaxed">
              If TaskFlow is involved in a merger, acquisition, or sale of assets, your information may be transferred. We will notify you before your information becomes subject to a different privacy policy.
            </p>
          </section>

          <!-- Data Security -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Data Security</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We implement appropriate technical and organizational measures to protect your information:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li><strong>Encryption:</strong> Data in transit (HTTPS/TLS) and at rest</li>
              <li><strong>Authentication:</strong> Secure token-based authentication (Laravel Sanctum)</li>
              <li><strong>Access Controls:</strong> Role-based access and user isolation</li>
              <li><strong>Monitoring:</strong> Security monitoring and incident response</li>
              <li><strong>Regular Updates:</strong> Security patches and updates</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              However, no method of transmission over the Internet is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.
            </p>
          </section>

          <!-- Data Retention -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Data Retention</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We retain your information for as long as necessary to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li>Provide our Service to you</li>
              <li>Comply with legal obligations</li>
              <li>Resolve disputes and enforce agreements</li>
              <li>Maintain business records</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              When you delete your account, we will delete or anonymize your personal information within 30 days, except where we are required to retain it by law.
            </p>
          </section>

          <!-- Your Rights -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Your Privacy Rights</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              Depending on your location, you may have the following rights:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li><strong>Access:</strong> Request a copy of your personal information</li>
              <li><strong>Correction:</strong> Request correction of inaccurate information</li>
              <li><strong>Deletion:</strong> Request deletion of your information</li>
              <li><strong>Portability:</strong> Request transfer of your data</li>
              <li><strong>Objection:</strong> Object to processing of your information</li>
              <li><strong>Restriction:</strong> Request restriction of processing</li>
              <li><strong>Withdrawal:</strong> Withdraw consent at any time</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              To exercise these rights, please contact us at privacy@taskflow.com. We will respond within 30 days.
            </p>
          </section>

          <!-- Cookies -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Cookies and Tracking</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              We use cookies and similar tracking technologies to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 space-y-2 mb-4">
              <li><strong>Essential Cookies:</strong> Required for authentication and security</li>
              <li><strong>Functional Cookies:</strong> Remember your preferences</li>
              <li><strong>Analytics Cookies:</strong> Understand how you use our Service</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
              You can control cookies through your browser settings. However, disabling cookies may affect your ability to use certain features of TaskFlow.
            </p>
          </section>

          <!-- Children's Privacy -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Children's Privacy</h2>
            <p class="text-gray-700 leading-relaxed">
              TaskFlow is not intended for users under 18 years of age. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.
            </p>
          </section>

          <!-- International Data Transfers -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. International Data Transfers</h2>
            <p class="text-gray-700 leading-relaxed">
              Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.
            </p>
          </section>

          <!-- Third-Party Links -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Third-Party Links</h2>
            <p class="text-gray-700 leading-relaxed">
              Our Service may contain links to third-party websites. We are not responsible for the privacy practices of these websites. We encourage you to read their privacy policies.
            </p>
          </section>

          <!-- Changes to Privacy Policy -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Changes to This Privacy Policy</h2>
            <p class="text-gray-700 leading-relaxed">
              We may update this Privacy Policy from time to time. We will notify you of material changes by email or through a prominent notice on our Service. Your continued use after changes become effective constitutes acceptance of the updated policy.
            </p>
          </section>

          <!-- Contact -->
          <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Contact Us</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              If you have questions or concerns about this Privacy Policy, please contact us:
            </p>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700"><strong>Email:</strong> privacy@taskflow.com</p>
              <p class="text-gray-700"><strong>Data Protection Officer:</strong> dpo@taskflow.com</p>
              <p class="text-gray-700"><strong>Address:</strong> TaskFlow Privacy Team</p>
              <p class="text-gray-700 ml-16">[Your Address]</p>
            </div>
          </section>

          <!-- Summary -->
          <section class="mb-8">
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
              <h3 class="text-lg font-semibold text-indigo-900 mb-2">Privacy Commitment</h3>
              <p class="text-indigo-800 mb-3">
                At TaskFlow, we are committed to protecting your privacy and being transparent about our data practices. We:
              </p>
              <ul class="list-disc pl-6 text-indigo-800 space-y-1">
                <li>Only collect data necessary to provide our Service</li>
                <li>Never sell your personal information</li>
                <li>Implement strong security measures</li>
                <li>Respect your privacy rights</li>
                <li>Are transparent about our practices</li>
              </ul>
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

.prose h3 {
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
}

.prose p {
  margin-bottom: 1rem;
}

.prose ul {
  margin-bottom: 1rem;
}
</style>
