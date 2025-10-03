const { supabase } = require('./supabase-client');

(async () => {
  try {
    // Delete all categories where name is NOT 'All News'
    const { data, error } = await supabase
      .from('categories')
      .delete()
      .neq('name', 'All News')
      .select();

    if (error) {
      console.error('❌ Error deleting categories:', error.message);
      process.exit(1);
    }

    console.log('✅ Deleted categories:', data);

    // Show remaining categories
    const { data: remaining, error: err } = await supabase
      .from('categories')
      .select('*');

    if (err) {
      console.error('Error fetching remaining:', err);
    } else {
      console.log('\n📋 Remaining categories:', remaining);
    }

    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error);
    process.exit(1);
  }
})();
