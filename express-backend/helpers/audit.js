const { supabase } = require('../supabase-client');

async function logAudit({
  table_name,
  record_id,
  action,
  admin_username,
  old_values = null,
  new_values = null,
  ip_address,
  user_agent
}) {
  try {
    await supabase
      .from('audit_logs')
      .insert([{
        table_name,
        record_id,
        action,
        admin_username,
        old_values,
        new_values,
        ip_address,
        user_agent
      }]);

    console.log(`📝 Audit: ${admin_username} ${action} ${table_name}#${record_id}`);
  } catch (error) {
    console.error('❌ Audit log failed:', error.message);
  }
}

module.exports = { logAudit };
