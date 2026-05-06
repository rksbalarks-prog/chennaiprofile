export default function PrivacyPolicy() {
  const Section = ({ title, children }) => (
    <div style={{ background:'#fff', borderRadius:12, padding:'18px', marginBottom:10, border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }}>
      <div style={{ fontSize:15.4, fontWeight:700, color:'#8B0000', marginBottom:8, paddingBottom:6, borderBottom:'2px solid #fef2f2' }}>{title}</div>
      <div style={{ fontSize:14.3, color:'#555', lineHeight:1.8 }}>{children}</div>
    </div>
  );

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:70 }}>
      <div style={{ background:'#fff', padding:'20px 16px', borderBottom:'1px solid #f0f0f0', textAlign:'center' }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#222', margin:0 }}>Privacy Policy</h1>
        <p style={{ fontSize:13.2, color:'#999', marginTop:4 }}>Last updated: April 2026</p>
      </div>

      <div style={{ maxWidth:600, margin:'0 auto', padding:'12px' }}>

        <Section title="1. Information We Collect">
          <p>We collect personal information that you provide during registration, including your name, age, gender, mobile number, caste, religion, education, occupation, family details, and photographs. We also collect horoscope-related information if provided.</p>
        </Section>

        <Section title="2. How We Use Your Information">
          <p>Your information is used to create your matrimonial profile, enable search and match functionality, facilitate communication between interested parties, and improve our services. We may also use your mobile number for OTP verification and account security.</p>
        </Section>

        <Section title="3. Information Sharing">
          <p>Your profile information is visible to other registered users on our platform. We do not sell or share your personal data with third-party marketers. Contact details are only shared with verified users who have completed OTP verification.</p>
        </Section>

        <Section title="4. Data Security">
          <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure. All data transmission is encrypted, and we use secure servers to store your information.</p>
        </Section>

        <Section title="5. Photos & Media">
          <p>Photos uploaded to your profile are stored securely on our servers. They are visible to other registered users. You can update or remove your photos at any time through your profile settings.</p>
        </Section>

        <Section title="6. Cookies & Tracking">
          <p>We use session cookies for authentication and to maintain your login state. We do not use third-party tracking cookies for advertising purposes.</p>
        </Section>

        <Section title="7. Your Rights">
          <p>You have the right to access, modify, or delete your personal information at any time. You can request profile deletion by contacting our support team. We will process your request within 7 business days.</p>
        </Section>

        <Section title="8. Contact Us">
          <p>For privacy-related queries, contact us at:</p>
          <p style={{ marginTop:6 }}>
            Email: <strong>info@chennaiprofile.in</strong><br/>
            Phone: <strong>+91 90253 16833</strong>
          </p>
        </Section>
      </div>
    </div>
  );
}
