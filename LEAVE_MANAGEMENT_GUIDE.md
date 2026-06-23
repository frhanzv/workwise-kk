# Leave Reason Management System - Implementation Guide

## Overview
A comprehensive leave management system has been added to the Workwise application, allowing HR personnel to manage leave reasons and mark worker leaves directly from the worker calendar view.

## Features Implemented

### 1. Leave Reason List (Config)
**Location:** Config > Leave Reason List (`/config/leave-reasons`)

**Features:**
- Add, edit, and delete custom leave reasons
- Categorize leaves into types: Paid Leave, Medical Leave, Unpaid Leave, Other
- Add descriptions for each leave reason
- Toggle active/inactive status for leave reasons
- Pre-loaded with 10 common leave reasons

**Default Leave Reasons:**
- Annual Leave (Paid Leave)
- Sick Leave (Medical Leave)
- Medical Appointment (Medical Leave)
- Family Emergency (Paid Leave)
- Personal Leave (Unpaid Leave)
- Maternity Leave (Paid Leave)
- Paternity Leave (Paid Leave)
- Bereavement Leave (Paid Leave)
- Training/Conference (Paid Leave)
- Unpaid Personal Leave (Unpaid Leave)

### 2. Worker Calendar Leave Marking (HR Only)
**Location:** Workers > View Worker > Attendance Tab

**HR-Only Features:**
- "Mark Leave" button appears only for HR users
- Click to open leave marking modal
- Select leave date, reason, and add optional notes
- Leave records are immediately reflected on the calendar
- Calendar shows different colors for different leave types:
  - **Paid Leave**: Yellow background
  - **Medical Leave**: Orange background
  - **Unpaid Leave**: Gray background

### 3. Database Structure

#### Tables Created:
1. **leave_reasons**
   - `id`: Primary key
   - `name`: Leave reason name
   - `type`: Enum (Paid Leave, Medical Leave, Unpaid Leave, Other)
   - `description`: Optional description
   - `is_active`: Status flag
   - `created_at`, `updated_at`: Timestamps

2. **worker_leave_records**
   - `id`: Primary key
   - `worker_id`: Foreign key to workers table
   - `leave_reason_id`: Foreign key to leave_reasons table
   - `leave_date`: Date of leave
   - `notes`: Optional notes
   - `created_by`: User ID who created the record
   - `created_at`, `updated_at`: Timestamps

## How to Use

### For HR Personnel:

#### Managing Leave Reasons:
1. Go to **Config > Leave Reason List**
2. Click "Add Leave Reason" to create new reasons
3. Fill in the reason name, select type, and add description
4. Save the reason
5. Edit or toggle status as needed

#### Marking Worker Leaves:
1. Go to **Workers > Worker List**
2. Click on a worker to view their profile
3. On the Attendance tab, click "Mark Leave" button
4. Select the leave date
5. Choose a leave reason from the dropdown
6. Add optional notes
7. Click "Mark Leave" to save

### Calendar Display:
- **Green**: Worker was present (checked in)
- **Yellow**: Paid leave
- **Orange**: Medical leave
- **Gray**: Unpaid leave or other
- **Red**: Absent (no check-in, no leave marked)
- **Red/Yellow background**: Public holiday (Federal/State)

## HR User Detection
Currently, the system identifies HR users by checking if their:
- Email contains "hr" (case-insensitive), OR
- Username contains "hr" (case-insensitive)

**To enable HR features for a user:**
- Update user email to include "hr" (e.g., `hr@company.com`, `john.hr@company.com`)
- OR update username to include "hr" (e.g., `hr.john`, `hr_admin`)

**Future Enhancement:**
Consider adding a dedicated `role` or `position` column to the users table for more robust access control.

## API Endpoints

### Leave Management
- **POST** `/workers/mark-leave` - Mark a leave for a worker (HR only)
  - Parameters: `worker_id`, `leave_reason_id`, `leave_date`, `notes`
- **POST** `/workers/remove-leave` - Remove a leave record (HR only)
  - Parameters: `worker_id`, `leave_date`

### Leave Reason Config
- **GET** `/config/leave-reasons` - View leave reasons list
- **POST** `/config/leave-reasons/store` - Create new leave reason
- **POST** `/config/leave-reasons/update` - Update leave reason
- **POST** `/config/leave-reasons/toggle/{id}` - Toggle leave reason status
- **POST** `/config/leave-reasons/delete/{id}` - Delete leave reason

## Files Modified/Created

### Controllers:
- `app/Controllers/Config.php` - Added leave reason CRUD methods
- `app/Controllers/Workers.php` - Added leave marking methods, integrated leave records into calendar view

### Models:
- `app/Models/LeaveReasonModel.php` (NEW)
- `app/Models/WorkerLeaveRecordModel.php` (NEW)

### Views:
- `app/Views/config/index.php` - Added Leave Reason List card
- `app/Views/config/leave_reasons.php` (NEW) - Leave reason management interface
- `app/Views/workers/partials/attendance_calendar.php` - Added leave marking modal and HR controls

### Database:
- `app/Database/Migrations/2026-01-02-100000_CreateLeaveReasonsTables.php` (NEW)
- `leave_reasons_tables.sql` - SQL schema (reference)

## Testing Checklist

- [ ] Config > Leave Reasons page loads correctly
- [ ] Can add new leave reason
- [ ] Can edit existing leave reason
- [ ] Can toggle leave reason status
- [ ] Can delete leave reason
- [ ] HR user sees "Mark Leave" button on worker calendar
- [ ] Non-HR user does NOT see "Mark Leave" button
- [ ] Can mark leave for a specific date
- [ ] Leave appears on calendar with correct color
- [ ] Calendar attendance stats update correctly
- [ ] Leave records persist after page refresh
- [ ] Cannot mark duplicate leave for same date

## Security Notes

1. **HR-Only Access**: Leave marking functions check user credentials before allowing access
2. **Input Validation**: All form inputs are validated on both client and server side
3. **SQL Injection Protection**: Using CodeIgniter's Query Builder for safe database operations
4. **CSRF Protection**: All forms include CSRF tokens

## Future Enhancements

1. Add proper role-based access control (RBAC) system
2. Leave approval workflow (request → approve → reject)
3. Leave balance tracking (annual leave days remaining)
4. Leave reports and analytics
5. Email notifications for leave marked
6. Bulk leave marking (mark leave for multiple workers)
7. Leave calendar export (PDF, Excel)
8. Leave history view for workers

## Troubleshooting

### "Mark Leave" button not appearing:
- Check if user email or username contains "hr"
- Verify user is logged in
- Check browser console for JavaScript errors

### Leave not saving:
- Verify database migration ran successfully: `php spark migrate`
- Check that leave_reasons and worker_leave_records tables exist
- Verify worker_id exists in workers table
- Check server logs for errors

### Calendar not updating:
- Hard refresh the page (Ctrl+F5)
- Clear browser cache
- Verify leave record was created in database
- Check date format is YYYY-MM-DD

## Support

For issues or questions, contact the development team or refer to the main application documentation.

---

**Version:** 1.0.0  
**Date:** January 2, 2026  
**Author:** Workwise Development Team
