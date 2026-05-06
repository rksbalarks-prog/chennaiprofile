export default function TermsAndConditions() {
  const Section = ({ title, children }) => (
    <div style={{ background:'#fff', borderRadius:12, padding:'18px', marginBottom:10, border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }}>
      <div style={{ fontSize:15.4, fontWeight:700, color:'#8B0000', marginBottom:8, paddingBottom:6, borderBottom:'2px solid #fef2f2' }}>{title}</div>
      <div style={{ fontSize:14.3, color:'#555', lineHeight:1.8 }}>{children}</div>
    </div>
  );

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:70 }}>
      <div style={{ background:'#fff', padding:'20px 16px', borderBottom:'1px solid #f0f0f0', textAlign:'center' }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#222', margin:0 }}>Terms & Conditions</h1>
        <p style={{ fontSize:13.2, color:'#999', marginTop:4 }}>Last updated: April 2026</p>
      </div>

      <div style={{ maxWidth:600, margin:'0 auto', padding:'12px' }}>

        <Section title="1. Acceptance of Terms">
          <p>By accessing and using Chennai Profile Matrimony, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>
        </Section>

        <Section title="2. Eligibility">
          <p>You must be at least 18 years of age (21 for males as per Indian law) to register on our platform. By registering, you confirm that you are legally eligible for marriage under Indian law.</p>
        </Section>

        <Section title="3. Registration & Profile">
          <p>You agree to provide accurate, complete, and truthful information during registration. You are responsible for maintaining the confidentiality of your account. Providing false information may result in immediate termination of your account.</p>
        </Section>

        <Section title="4. User Conduct">
          <p>You agree not to:</p>
          <ul style={{ paddingLeft:18, marginTop:6, display:'flex', flexDirection:'column', gap:4 }}>
            <li>Upload false, misleading, or fraudulent information</li>
            <li>Harass, abuse, or threaten other users</li>
            <li>Use the platform for any unlawful purpose</li>
            <li>Create multiple accounts</li>
            <li>Share other users' contact information without consent</li>
            <li>Upload inappropriate or offensive photos</li>
          </ul>
        </Section>

        <Section title="5. Free Service">
          <p>Chennai Profile Matrimony is a free service. We do not charge any fees for registration, profile creation, searching, or viewing contact details. Premium features, if introduced in the future, will be clearly marked and optional.</p>
        </Section>

        <Section title="6. Profile Verification">
          <p>We reserve the right to verify profiles and may request additional documentation. Profiles found to be fake or misleading will be removed without notice.</p>
        </Section>

        <Section title="7. Content Ownership">
          <p>You retain ownership of the content you upload. By uploading photos and information, you grant us a non-exclusive license to display this content on our platform for matrimonial purposes.</p>
        </Section>

        <Section title="8. Limitation of Liability">
          <p>Chennai Profile Matrimony acts as a platform to connect individuals. We are not responsible for the actions, behavior, or statements of any user. We do not guarantee the accuracy of information provided by users.</p>
        </Section>

        <Section title="9. Account Termination">
          <p>We reserve the right to suspend or terminate accounts that violate these terms. You may also request account deletion at any time by contacting our support team.</p>
        </Section>

        <Section title="10. Contact">
          <p>For questions about these terms, contact us at:</p>
          <p style={{ marginTop:6 }}>
            Email: <strong>info@chennaiprofile.in</strong><br/>
            Phone: <strong>+91 90253 16833</strong>
          </p>
        </Section>
      </div>
    </div>
  );
}
