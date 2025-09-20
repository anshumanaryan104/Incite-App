-- Create about_contact table
CREATE TABLE IF NOT EXISTS about_contact (
    id SERIAL PRIMARY KEY,
    -- About section
    about_title VARCHAR(255) DEFAULT 'About Us',
    about_description TEXT DEFAULT 'Welcome to News App - Your trusted source for latest news and updates',
    about_content TEXT DEFAULT 'We are dedicated to bringing you the most relevant and timely news from around the world. Our team of journalists and editors work around the clock to ensure you stay informed about what matters most.',
    about_mission TEXT DEFAULT 'To deliver accurate, unbiased, and comprehensive news coverage to our readers',
    about_vision TEXT DEFAULT 'To be the most trusted digital news platform',
    about_established VARCHAR(50) DEFAULT '2024',
    about_team_size VARCHAR(255) DEFAULT 'Growing team of passionate individuals',
    about_values JSONB DEFAULT '["Accuracy and Truth", "Unbiased Reporting", "Reader First Approach", "Innovation in News Delivery"]'::jsonb,

    -- Contact section
    contact_title VARCHAR(255) DEFAULT 'Contact Us',
    contact_description TEXT DEFAULT 'We''d love to hear from you',
    contact_email VARCHAR(255) DEFAULT 'contact@newsapp.com',
    contact_support_email VARCHAR(255) DEFAULT 'support@newsapp.com',
    contact_phone VARCHAR(50) DEFAULT '+91 98765 43210',
    contact_whatsapp VARCHAR(50) DEFAULT '+91 98765 43210',

    -- Address as JSONB
    contact_address JSONB DEFAULT '{
        "line1": "News App Headquarters",
        "line2": "Tech Park, Building A",
        "city": "Mumbai",
        "state": "Maharashtra",
        "country": "India",
        "pincode": "400001"
    }'::jsonb,

    -- Business hours as JSONB
    business_hours JSONB DEFAULT '{
        "weekdays": "9:00 AM - 6:00 PM",
        "saturday": "9:00 AM - 2:00 PM",
        "sunday": "Closed"
    }'::jsonb,

    contact_response_time VARCHAR(255) DEFAULT 'We typically respond within 24 hours',

    -- Legal section
    privacy_policy_url VARCHAR(255) DEFAULT '/privacy-policy',
    terms_conditions_url VARCHAR(255) DEFAULT '/terms-conditions',
    disclaimer TEXT DEFAULT 'All news content is for informational purposes only',

    -- App info
    app_version VARCHAR(50) DEFAULT '1.0.0',
    app_developer VARCHAR(255) DEFAULT 'News App Team',
    app_copyright VARCHAR(255) DEFAULT '© 2025 News App. All rights reserved.',

    -- Timestamps
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Insert default data (you can customize these values)
INSERT INTO about_contact (id)
VALUES (1)
ON CONFLICT (id) DO NOTHING;

-- Create an update trigger for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger
DROP TRIGGER IF EXISTS update_about_contact_updated_at ON about_contact;
CREATE TRIGGER update_about_contact_updated_at
BEFORE UPDATE ON about_contact
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Grant permissions (adjust as needed)
GRANT ALL ON about_contact TO authenticated;
GRANT ALL ON about_contact TO anon;
GRANT USAGE ON SEQUENCE about_contact_id_seq TO authenticated;
GRANT USAGE ON SEQUENCE about_contact_id_seq TO anon;

-- Verify the table was created
SELECT column_name, data_type
FROM information_schema.columns
WHERE table_name = 'about_contact'
ORDER BY ordinal_position;