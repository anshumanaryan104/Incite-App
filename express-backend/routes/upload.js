const express = require('express');
const router = express.Router();
const { supabase } = require('../supabase-client');
const multer = require('multer');
const path = require('path');
const crypto = require('crypto');

// Configure multer for memory storage
const storage = multer.memoryStorage();
const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024, // 5MB limit
    },
    fileFilter: (req, file, cb) => {
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (allowedTypes.includes(file.mimetype)) {
            cb(null, true);
        } else {
            cb(new Error('Invalid file type. Only JPEG, PNG, WEBP, and GIF are allowed.'));
        }
    }
});

// Upload category icon
router.post('/category-icon', upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({
                success: false,
                message: 'No image file provided'
            });
        }

        // Generate unique filename
        const fileExt = path.extname(req.file.originalname);
        const fileName = `${crypto.randomBytes(16).toString('hex')}${fileExt}`;
        const filePath = `category-icons/${fileName}`;

        // Upload to Supabase Storage
        const { data, error } = await supabase.storage
            .from('public')
            .upload(filePath, req.file.buffer, {
                contentType: req.file.mimetype,
                upsert: false
            });

        if (error) {
            console.error('Supabase upload error:', error);
            return res.status(500).json({
                success: false,
                message: 'Failed to upload image to storage',
                error: error.message
            });
        }

        // Get public URL
        const { data: publicUrlData } = supabase.storage
            .from('public')
            .getPublicUrl(filePath);

        res.json({
            success: true,
            message: 'Image uploaded successfully',
            data: {
                url: publicUrlData.publicUrl,
                path: filePath,
                filename: fileName
            }
        });

    } catch (error) {
        console.error('Upload error:', error);
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

// Upload article image
router.post('/article-image', upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({
                success: false,
                message: 'No image file provided'
            });
        }

        // Generate unique filename
        const fileExt = path.extname(req.file.originalname);
        const fileName = `${crypto.randomBytes(16).toString('hex')}${fileExt}`;
        const filePath = `article-images/${fileName}`;

        // Upload to Supabase Storage
        const { data, error } = await supabase.storage
            .from('public')
            .upload(filePath, req.file.buffer, {
                contentType: req.file.mimetype,
                upsert: false
            });

        if (error) {
            console.error('Supabase upload error:', error);
            return res.status(500).json({
                success: false,
                message: 'Failed to upload image to storage',
                error: error.message
            });
        }

        // Get public URL
        const { data: publicUrlData } = supabase.storage
            .from('public')
            .getPublicUrl(filePath);

        res.json({
            success: true,
            message: 'Image uploaded successfully',
            data: {
                url: publicUrlData.publicUrl,
                path: filePath,
                filename: fileName
            }
        });

    } catch (error) {
        console.error('Upload error:', error);
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

module.exports = router;
