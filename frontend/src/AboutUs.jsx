import { useTranslation } from "react-i18next";

export default function AboutUs() {
  const { t } = useTranslation();

  const Section = ({ title, children }) => (
    <div style={{ background:'#fff', borderRadius:12, padding:'18px', marginBottom:10, border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }}>
      <div style={{ fontSize:16.5, fontWeight:700, color:'#8B0000', marginBottom:10, paddingBottom:8, borderBottom:'2px solid #fef2f2' }}>{title}</div>
      {children}
    </div>
  );

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:70 }}>
      <div style={{ background:'#fff', padding:'20px 16px', borderBottom:'1px solid #f0f0f0', textAlign:'center' }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#222', margin:0 }}>About Us</h1>
        <p style={{ fontSize:14.3, color:'#999', marginTop:4 }}>Chennai Profile Matrimony</p>
      </div>

      <div style={{ maxWidth:600, margin:'0 auto', padding:'12px' }}>

        <Section title="Who We Are">
          <p style={{ fontSize:15.4, color:'#444', lineHeight:1.8 }}>
            Chennai Profile Matrimony is a trusted matrimonial platform dedicated to helping individuals find their perfect life partner. Based in the culturally rich town of Chennai, Tamil Nadu, we understand the importance of tradition, values, and compatibility in building lasting relationships.
          </p>
        </Section>

        <Section title="Our Mission">
          <p style={{ fontSize:15.4, color:'#444', lineHeight:1.8 }}>
            Our mission is to provide a safe, reliable, and completely free platform for individuals and families to find suitable matches. We believe that everyone deserves to find love and companionship without financial barriers.
          </p>
        </Section>

        <Section title="Why Choose Us">
          <div style={{ display:'flex', flexDirection:'column', gap:10 }}>
            {[
              { title:'100% Free Service', desc:'No hidden charges. Register, search, and connect for free.' },
              { title:'Verified Profiles', desc:'Every profile is reviewed and verified by our team.' },
              { title:'Secure & Private', desc:'Your personal information is protected and confidential.' },
              { title:'Bilingual Support', desc:'Available in both English and Tamil for your convenience.' },
              { title:'Dedicated Support', desc:'Our team is available to assist you throughout your journey.' },
            ].map((item, i) => (
              <div key={i} style={{ display:'flex', gap:12, padding:'12px', background:'#fafafa', borderRadius:8, border:'1px solid #f0f0f0' }}>
                <div style={{ width:32, height:32, borderRadius:8, background:'#fef2f2', display:'flex', alignItems:'center', justifyContent:'center', fontSize:15.4, fontWeight:700, color:'#8B0000', flexShrink:0 }}>{i+1}</div>
                <div>
                  <div style={{ fontSize:15.4, fontWeight:700, color:'#222' }}>{item.title}</div>
                  <div style={{ fontSize:14.3, color:'#777', marginTop:2 }}>{item.desc}</div>
                </div>
              </div>
            ))}
          </div>
        </Section>

        <Section title="Our Services">
          <div style={{ fontSize:15.4, color:'#444', lineHeight:1.8 }}>
            <ul style={{ paddingLeft:18, display:'flex', flexDirection:'column', gap:6 }}>
              <li>Profile creation and management</li>
              <li>Advanced search with filters (caste, religion, age, etc.)</li>
              <li>Horoscope matching and astrology details</li>
              <li>Direct contact sharing between interested parties</li>
              <li>WhatsApp and phone support</li>
              <li>Profile verification and screening</li>
            </ul>
          </div>
        </Section>

        <Section title="Contact Information">
          <div style={{ fontSize:15.4, color:'#444', lineHeight:2 }}>
            <div>Phone: <strong>+91 90253 16833</strong></div>
            <div>Email: <strong>info@chennaiprofile.in</strong></div>
            <div>Address: <strong>No. 45, Big Street, Chennai - 600001, Tamil Nadu</strong></div>
          </div>
        </Section>
      </div>
    </div>
  );
}
