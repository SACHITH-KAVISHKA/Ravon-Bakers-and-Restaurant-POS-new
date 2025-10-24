# Branch Wastage Feature - User Guide

## For Staff Members

### Accessing Branch Wastage

1. **Login** to your staff account
2. Look at the left sidebar navigation
3. Find and click on **"Branch Wastage"** (trash icon)

### Adding a New Branch Wastage Record

#### Step 1: Navigate to Add Form
- Click on **"Add Branch Wastage"** button (red button at top right)
- Or access directly from the menu

#### Step 2: Fill in the Form

**Date & Time:**
- The current date and time will be pre-filled
- You can adjust if needed

**Items Table:**
- Select an item from the dropdown
- Available stock will automatically display
- Enter the wasted quantity
- Click **"Add More Items"** to add additional items (if needed)

**Remarks (Optional):**
- Add any notes about why the wastage occurred
- Examples: "Expired", "Damaged during handling", "Quality issue"

#### Step 3: Submit
- Click **"Record Branch Wastage"** button
- System will validate:
  - That you have selected at least one item
  - That quantities don't exceed available stock
  - That no duplicate items are selected

#### Step 4: Confirmation
- Success message will appear
- Your branch inventory will be automatically updated
- You'll be redirected to the Branch Inventory page

### Viewing Branch Wastage Records

#### Accessing the View
- Click **"Branch Wastage"** from the sidebar
- You'll see a list of all wastage records for your branch

#### Using Filters
**From Date:** Start date for filtering records
**To Date:** End date for filtering records
**Item Name:** Search for specific items
Click **"Filter"** button to apply

#### Viewing Details
- Each record shows:
  - Date & time
  - Who recorded it
  - Items involved
  - Total wasted quantity
  - Brief remarks
- Click **"View"** button to see full details including:
  - Previous stock levels
  - Wasted quantities
  - Remaining stock after wastage

### Important Notes

⚠️ **Stock Validation:**
- You can only waste items that exist in your branch inventory
- You cannot waste more than the available stock
- The system will prevent over-wastage

📊 **Stock Impact:**
- Branch wastage reduces ONLY your branch's stock
- It does NOT affect main inventory
- It does NOT affect other branches

👥 **Visibility:**
- You can only see wastage records from YOUR branch
- Each wastage record tracks who recorded it

### Example Scenario

**Situation:** You discovered 5 expired bread loaves

**Steps:**
1. Go to "Branch Wastage" → "Add Branch Wastage"
2. Select date/time of discovery
3. Select "Bread" from items dropdown
4. Enter "5" as wasted quantity
5. Add remark: "Expired - past best before date"
6. Click "Record Branch Wastage"
7. Done! Your branch stock will show 5 fewer bread loaves

### Troubleshooting

**Problem:** Can't see any items in the dropdown
**Solution:** Your branch might not have any stock. Contact your supervisor.

**Problem:** Getting "exceeds available stock" error
**Solution:** Check the "Available Stock" column - you're trying to waste more than you have.

**Problem:** Don't see "Branch Wastage" menu
**Solution:** Make sure you're logged in as staff and assigned to a branch.

### Benefits of Using This Feature

✅ Accurate branch inventory tracking
✅ Better waste management and reporting
✅ Helps identify problem items or patterns
✅ Maintains accountability with user tracking
✅ Easy filtering and searching of past records

### Best Practices

1. **Record wastage immediately** when it occurs
2. **Be specific in remarks** - helps identify patterns
3. **Double-check quantities** before submitting
4. **Review past records** to spot trends
5. **Report high wastage** to management

---

## For Supervisors/Management

### Understanding Branch Wastage vs. Supervisor Wastage

**Supervisor Wastage:**
- Reduces main inventory
- Affects all branches
- Used for production/warehouse wastage

**Branch Wastage (Staff):**
- Reduces branch-specific inventory
- Only affects the recording staff's branch
- Used for branch-level wastage (retail, service)

### Monitoring Branch Wastage

- Each wastage record includes the staff member who recorded it
- Records are linked to specific branches
- You can identify which branches have higher wastage
- Use this data for training or process improvements

### Common Wastage Reasons to Watch For

- Expired items (check ordering/stock rotation)
- Damaged items (check handling procedures)
- Quality issues (check supplier quality)
- Customer returns (check satisfaction levels)
- Preparation errors (check staff training)

---

## Technical Details

**Database:** Wastage records are stored with branch_id
**Inventory Impact:** Only the branch's inventory is modified
**User Tracking:** Each record logs the staff member
**Validation:** Real-time stock checks prevent errors
**Security:** Staff can only manage their own branch's wastage
