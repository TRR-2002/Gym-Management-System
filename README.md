

Introduction:

The Gym Management System is a web-based application designed to streamline and manage various operations within a fitness center. Built using PHP for server-side logic, MySQL (MariaDB) for database management, HTML for structure, CSS for styling, and minimal JavaScript for client-side enhancements, this project aims to provide a user-friendly interface for both gym members and staff. The system facilitates member registration, login, plan management, progress tracking, and feedback submission. For staff, it provides tools to manage assigned members, create and assign fitness plans, and view their own profiles and schedules. The core objective is to create an efficient digital platform that enhances the gym experience for members and simplifies administrative and operational tasks for staff.

In addition to member and staff features, the system includes a comprehensive Admin portal. This section gives administrators full control over the entire gym management system. Admins have their own secure login and a central dashboard to access all management functions. They can manage all member accounts, including adding new members, editing their details, or removing them. Similarly, admins can manage all staff accounts, including creating new staff profiles, updating their information, and managing staff schedules or roles. Admins also have superuser privileges over fitness plans, allowing them to create, view, edit, or delete any plan in the system, regardless of which staff member originally created it. Furthermore, they can assign any plan to any member. The admin portal also includes a section for reviewing all member feedback and a dedicated area for viewing system-wide data analytics and reports, providing insights into member activity, popular plans, and overall gym operations.

Features:
A. Member Features:
1. Registration: New users can register for a member account by providing personal details.
2. Login/Logout: Secure login for registered members and a logout functionality.
3. Dashboard: A personalized dashboard displaying current subscription status, assigned trainer (if any), an overview of their current fitness plan, and progress percentage.
4. Package Management: Members can view available subscription packages and activate or change their subscription type.
5. Plan Viewing: Members can view the details of their assigned fitness plan, including dietary guidelines and a structured exercise routine (day, time, exercise).
6. Progress Tracking: Members can log their current weight. The system calculates and displays their progression percentage towards their goal weight, based on the starting weight recorded for their current plan instance.
7. Profile Management: Members can view their profile details and update editable information such as email, address, and phone number.
8. Feedback Submission: A dedicated section for members to submit feedback regarding various aspects of the gym (e.g., equipment, trainers, classes).



B. Staff Features:
1. Login/Logout: Secure login for staff members (accounts are pre-created by an administrator) and logout.
2. Dashboard: A personalized dashboard showing summary information such as the number of members assigned to them, the number of fitness plans they have created, and their work schedule for the current day.
3. Profile & Schedule Viewing: Staff can view their own profile details (from the Workers table), listed certifications (from the Certifications table), and their work schedule (from the Staff_Routine table).
4. Member Management: Staff can view a list of members currently assigned to them, with a summary of each member's assigned plan and progress.
5. Detailed Member View: Staff can view a detailed profile of an assigned member (or an unassigned member they might take on), including their current plan details and progress.
6. Plan Assignment: Staff can assign or change a fitness plan for a member, selecting from plans they (the staff member) have personally created. Assigning a plan also makes the staff member the primary trainer for that member.
7. Plan Creation: Staff can create new fitness plan templates, defining a plan name, default/template starting and goal weights, dietary guidelines, and a detailed exercise routine (day, time, specific exercises).
8. Plan Editing: Staff can edit the fitness plan templates they have created, modifying details and the associated exercise routine. (Routine updates currently replace the entire existing routine set for that plan).
9. Plan Deletion: Staff can delete plans they have created. When a plan is deleted, members assigned to it will have their plan assignment set to null (they become unassigned from that plan), and all associated routine entries for that plan are removed.

C. Admin Features:
  1.Secure Admin Access: Dedicated login, session protection, profile management, and logout.
  2. Central Admin Dashboard: Overview and navigation to all admin functions.
  3.  Full Member CRUD: Create, read, update, and delete member accounts and details.
  4.  Full Staff CRUD: Create, read, update, and delete staff accounts and details.
  5. Full Equipment CRUD: Create, read, update, and delete gym equipment inventory.
  6.Feedback Review & Resolution: View member feedback and mark items as resolved (deleting them).
  7.  Fitness Plan Template CRUD: Create, read, update, and delete fitness plan templates.
  8.  Plan Routine Management: Add, view, and delete exercise routines associated with each plan template.
  9.  Plan Assignment to Members: Assign or unassign fitness plans to specific members.
  10. Staff Schedule Management: Add, view, and delete work schedule entries for staff members.
  11. System Analytics Overview: View key statistics (member counts, staff counts, package distribution, etc.).



Team Member's name & Contribution:
STAFF & MEMBERS - Tushit Roy
ADMIN - Nur Moshin


