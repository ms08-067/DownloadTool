var app = {
    env: function() {
        var environment_variable = document.head.querySelector("[name~=environment][content]").content;
        return environment_variable;
    },
    translations: {
        en: {
            title_text: 'Confirmation!',
            archive_document: 'Archive Document',
            okay_text: 'OK',
            cancel_text: 'Cancel',
            do_you_want_to_delete_this_item: 'Do you want to delete this item?',
            you_have_unsaved_changes: 'You have unsaved changes.',
            do_you_want_to_discard_those_changes: 'Do you want to discard those changes?',
            do_you_want_to_save_those_changes: 'Do you want to save those changes?',
            do_you_want_to_clear_manual_entered_entry_time: 'Do you want to clear manually entered entry time?',
            do_you_want_to_clear_all_manual_entered_exit_time: 'Do you want to clear all manually entered exit time?',
            are_you_sure_you_want_to_delete_this_record: 'Are you sure you want to delete this record?',
            you_may_alternatively_archive_the_document_after_deleteing_the_record: 'Alternatively, you may archive the document by clicking Archive Document below, which will also delete this record but keep the file',
            are_you_sure_you_want_to_enable_this_shift: 'Are you sure you want to enable this shift?',
            are_you_sure_you_want_to_delete_this_shift: 'Are you sure you want to delete this shift?',
            are_you_sure_you_want_to_disable_this_record: 'Are you sure you want to disable this shift?',

            are_you_sure_you_want_to_add_a_new_row_after: 'Are you sure you want to add a new row after?',
            are_you_sure_you_want_to_remove_this_row: 'Are you sure you want to remove this row?',

            are_you_sure_you_want_to_enable_this_schedule: 'Are you sure you want to enable this schedule?',
            are_you_sure_you_want_to_delete_this_schedule: 'Are you sure you want to delete this schedule?',
            are_you_sure_you_want_to_disable_this_schedule: 'Are you sure you want to disable this schedule?',

            are_you_sure_you_want_to_redownload_this_case: 'Are you sure you want to re-download Job ',

            are_you_sure_you_want_to_replace_disable_this_record: 'Are you sure you want to replace then disable this shift?',
            are_you_sure_you_want_to_remove_all_default_working_shifts_for_this_employee: 'Are you sure you want to remove all default working shifts for this employee?',
            are_you_sure_you_want_to_remove_overtime_request_for_this_employee: 'Are you sure you want to remove overtime request for this employee?',
            are_you_sure_you_want_to_remove_pause_time_for_this_employee: 'Are you sure you want to remove this pause time for the employee?',
            are_you_sure_you_want_to_update_company_info_record: 'Are you sure you want to update company info record',
            are_you_sure_you_want_to_update_personal_info_record: 'Are you sure you want to update personal info record',
            are_you_sure_you_want_to_update_banking_info_record: 'Are you sure you want to update banking info record',
            end_employee_employment_on_this_date: 'End employee employment on this date = ',
            re_activate_employee_employment_on_this_date: 'Re-Activate employee employment on this date = ',
            are_you_sure_you_want_to_update_the_employee_avatar: 'Are you sure you want to update the employee Avatar?',
            are_you_sure_you_want_to_delete_this_file: 'Are you sure you want to delete this file?',
            this_cannot_be_undone: 'This CANNOT be undone.',
            are_you_sure: 'Are you sure?',
            save_changes: 'Save changes?',
            do_you_want_to_upload_this_file: 'Do you want to upload this file?',
            do_you_want_to_upload_files: 'Do you want to upload these file(s)?',
            okay: 'Okay',
            attendance_status_cannot_be_deleted_there_are_time_records_associated_with_this_status: 'Attendance Status cannot be deleted. There are time records associated with this status.',
            public_holiday_cannot_be_deleted_it_is_imported_holiday_from_api: 'Public Holiday Cannot Be Deleted It Is A Holiday Imported From Festivo API.',
            attendance_status_deletion_was_aborted: 'Attendance Status deletion was aborted!',
            public_holiday_deletion_was_aborted: 'Public Holiday deletion was aborted!',
            working_shift_cannot_be_deleted_there_are_time_records_associated_with_this_status: 'Working Shift cannot be deleted. There are default/assigned shift records associated with this working shift.',
            working_shift_deletion_was_aborted: 'Working Shift deletion was aborted!',
            default_shift_cannot_be_deleted: 'Default Shift cannot be deleted.',
            overtime_request_cannot_be_deleted: 'Overtime Request cannot be deleted.',
            default_shift_removal_was_aborted: 'Default Shift removal was aborted!',
            overtime_request_removal_was_aborted: 'Overtime Request removal was aborted!',
            not_currently_supported: 'Not currently Supported',
            company_info_record_update_was_aborted: 'Company Info Record Update was aborted!',
            personal_info_record_update_was_aborted: 'Personal Info Record Update was aborted!',
            banking_info_record_update_was_aborted: 'Banking Info Record Update was aborted!',
            employe_termination_was_aborted: 'Employe Termination was aborted!',
            record_deletion_was_aborted: 'Record deletion was aborted!',
            employe_re_activation_was_aborted: 'Employe Re-Activation was aborted!',
            name_saved: 'Name saved',
            employee_avatar_change_was_aborted: 'Employee Avatar Change was aborted!',
            file_deletion_was_aborted: 'File deletion was aborted!',
            file_deletion_was_successful: 'File deletion was successful!',
            file_deletion_was_unsuccessful: 'File deletion was un-successful!',
            contract_record_deletion_was_aborted: 'Contract Record deletion was aborted!',
            contract_annex_record_deletion_was_aborted: 'Contract Annex Record deletion was aborted!',
            family_member_record_deletion_was_aborted: 'Family Memeber Record deletion was aborted!',
            general_document_record_deletion_was_aborted: 'General Document Record deletion was aborted!',
            company_position_cannot_be_deleted_there_are_employess_still_associated_with_this_position: 'Company Position cannot be deleted. There are employess still associated with this position.',
            company_position_deletion_was_aborted: 'Company Position deletion was aborted!',
            company_department_cannot_be_deleted_there_are_employess_still_associated_with_this_position: 'Company Department cannot be deleted. There are employess still associated with this position.',
            company_department_deletion_was_aborted: 'Company Department deletion was aborted!',
            permission_record_deletion_was_aborted: 'Permission Record deletion was aborted!',
            role_permission_assignment_change_was_reset: 'Role Permission Assignment Change was reset!',
            file_upload_aborted: 'File upload aborted!',
            import_complete: 'Import Complete',
            no_new_employees_found: 'No new employees found',
            you_need_to_enter_a_valid_date_in_the_format: 'You need to enter a valid date in the format',
            number_out_of_number: 'of',
            number_remaining_number: 'remaining',
            or_choose_from_the_calendar: 'or choose from the calendar',

            filter_by_birthday_month: 'Filter by Birthday Month',
            filter_by_date: 'Filter by Date',
            filter_by_date_from: 'Filter by Date From',
            to: 'To',
            filter_by_birthday_from: 'Filter by Birthday From',
            filter_by_sections: 'Filter by Sections',
            filter_by_jobstatus: 'Filter by Job Status',
            filter_by_assignees: 'Filter by Assignee(s)',
            filter_by_tool_client: 'Filter by Tool Client',
            filter_by_shifts_on: 'Filter by Shifts on',
            search_name_userid: 'Search Name/ UserID',
            filter_by_editor_level: 'Filter by Editor Level',
            filter_by_company_position: 'Filter by Company Position',
            filter_by_department: 'Filter by Department',
            filter_by_status: 'Filter by Status',
            filter_by_imported: 'Filter by Imported',
            filter_by_enabled_disabled: 'Filter by Enabled/Disabled',
            filter_by_currentlyinoffice: 'Filter by Currently In Office',
            filter_by_action: 'Filter by Action',

            select_employee_working_worked_at_br24vn_who_is_an_extended_or_immediate_family_member: 'Select Employee Working/ worked at BR24VN who is an extended or immediate family member',

            select_employee: 'Select Employee',

            select_employee_to_send_message_to: 'Select Employee(s) to send message to',
            select_channel_to_send_message_to: 'Select channel(s) to send message to',

            no_overtime_requests_added: 'No Overtime Requests Added',
            no_pause_times_added: 'No Pause Times Added',
            no_attendance_status_type_added: 'No Attendance Status Type Added',
            no_public_holidays_added: 'No Public Holidays Added',
            no_working_shifts_added: 'No Working Shifts Added',
            no_custom_rc_message_schedule_added: 'No Custom RC Message Schedule Added',
            no_advance_payment_request_added: 'No Advance Payment Request Added',
            no_default_shifts_added: 'No Default Shifts Added',
            no_contracts_added: 'No Contracts Added',
            no_family_members_added: 'No Family Members Added',
            no_general_documents_added: 'No General Documents Added',
            no_attendance_recorded: 'No Attendance Recorded',
            no_company_positions_added: 'No Company Positions Added',
            no_company_departments_added: 'No Company Departments Added',
            no_roles_added: 'No Roles Added,',
            section_search: 'Section Search',
            no_permissions_added: 'No Permissions Added',
            please_wait_loading: 'Please wait - loading...',
        },
        vi: {
            title_text: 'Phép thêm sức!',
            archive_document: 'Tài liệu lưu trữ',
            okay_text: 'Lưu',
            cancel_text: 'Bỏ qua',
            do_you_want_to_delete_this_item: 'Bạn có muốn xóa mục này?',
            you_have_unsaved_changes: 'Bạn có các thay đổi chưa lưu.',
            do_you_want_to_discard_those_changes: 'Bạn có muốn loại bỏ những thay đổi?',
            do_you_want_to_save_those_changes: 'Bạn có muốn lưu những thay đổi đó không?',
            do_you_want_to_clear_manual_entered_entry_time: 'Bạn có muốn xóa thời gian nhập thủ công?',
            do_you_want_to_clear_all_manual_entered_exit_time: 'Bạn có muốn xóa tất cả thủ công nhập thời gian thoát?',
            are_you_sure_you_want_to_delete_this_record: 'Bạn có chắc chắn muốn xóa hồ sơ này?',
            you_may_alternatively_archive_the_document_after_deleteing_the_record: 'Ngoài ra, bạn có thể lưu trữ tài liệu bằng cách nhấp vào Lưu trữ tài liệu bên dưới, điều này cũng sẽ xóa bản ghi này nhưng vẫn giữ tệp',
            are_you_sure_you_want_to_enable_this_shift: 'Bạn có chắc chắn muốn kích hoạt sự thay đổi này?',
            are_you_sure_you_want_to_delete_this_shift: 'Bạn có chắc chắn muốn xóa ca này?',
            are_you_sure_you_want_to_disable_this_record: 'Bạn có chắc chắn muốn tắt sự thay đổi này?',

            are_you_sure_you_want_to_add_a_new_row_after: 'Bạn có chắc chắn muốn thêm một hàng mới sau?',
            are_you_sure_you_want_to_remove_this_row: 'Bạn có chắc chắn muốn xóa hàng này?',

            are_you_sure_you_want_to_enable_this_schedule: 'Bạn có chắc chắn muốn kích hoạt lịch trình này?',
            are_you_sure_you_want_to_delete_this_schedule: 'Bạn có chắc chắn muốn xóa lịch trình này?',
            are_you_sure_you_want_to_disable_this_schedule: 'Bạn có chắc chắn muốn tắt lịch trình này?',

            are_you_sure_you_want_to_redownload_this_case: 'Bạn có chắc chắn muốn tải lại trường hợp này không?',

            are_you_sure_you_want_to_replace_disable_this_record: 'Bạn có chắc chắn muốn thay thế sau đó vô hiệu hóa sự thay đổi này?',
            are_you_sure_you_want_to_remove_all_default_working_shifts_for_this_employee: 'Bạn có chắc chắn muốn xóa tất cả các ca làm việc mặc định cho nhân viên này không?',
            are_you_sure_you_want_to_remove_overtime_request_for_this_employee: 'Bạn có chắc chắn muốn xóa yêu cầu làm thêm giờ cho nhân viên này không?',
            are_you_sure_you_want_to_remove_pause_time_for_this_employee: 'Bạn có chắc chắn muốn xóa thời gian tạm dừng này cho nhân viên?',
            are_you_sure_you_want_to_update_company_info_record: 'Bạn có chắc chắn muốn cập nhật hồ sơ thông tin công ty',
            are_you_sure_you_want_to_update_personal_info_record: 'Bạn có chắc chắn muốn cập nhật hồ sơ thông tin cá nhân',
            are_you_sure_you_want_to_update_banking_info_record: 'Bạn có chắc chắn muốn cập nhật hồ sơ thông tin ngân hàng',
            re_activate_employee_employment_on_this_date: 'Kích hoạt lại việc làm của nhân viên vào ngày này =',
            end_employee_employment_on_this_date: 'Kết thúc việc làm của nhân viên vào ngày này = ',
            are_you_sure_you_want_to_update_the_employee_avatar: 'Bạn có chắc chắn muốn cập nhật Avatar nhân viên?',
            are_you_sure_you_want_to_delete_this_file: 'Bạn có chắc chắn muốn xóa tập tin này?',
            this_cannot_be_undone: 'Điều này không thể được hoàn tác',
            are_you_sure: 'Bạn có chắc không?',
            save_changes: 'Lưu thay đổi?',
            do_you_want_to_upload_this_file: 'Bạn có muốn tải lên tập tin này?',
            do_you_want_to_upload_files: 'Bạn có muốn tải lên (các) tập tin này không?',
            okay: 'Đuợc',
            attendance_status_cannot_be_deleted_there_are_time_records_associated_with_this_status: 'Tình trạng tham dự không thể bị xóa. Có hồ sơ thời gian liên quan đến tình trạng này.',
            public_holiday_cannot_be_deleted_it_is_imported_holiday_from_api: 'Kỳ nghỉ công cộng không thể xóa được Đó là một kỳ nghỉ được nhập từ API Festivo.',
            attendance_status_deletion_was_aborted: 'Tình trạng tham dự xóa đã bị hủy bỏ!',
            public_holiday_deletion_was_aborted: 'Xóa kỳ nghỉ lễ đã bị hủy bỏ!',
            working_shift_cannot_be_deleted_there_are_time_records_associated_with_this_status: 'Shift làm việc không thể bị xóa. Có hồ sơ thay đổi mặc định / được chỉ định liên quan đến ca làm việc này.',
            working_shift_deletion_was_aborted: 'Xóa ca làm việc đã bị hủy bỏ!',
            default_shift_cannot_be_deleted: 'Shift mặc định không thể bị xóa. ',
            overtime_request_cannot_be_deleted: 'Yêu cầu làm thêm giờ không thể bị xóa.',
            default_shift_removal_was_aborted: 'Loại bỏ Shift mặc định đã bị hủy bỏ!',
            overtime_request_removal_was_aborted: 'Yêu cầu làm thêm giờ đã bị hủy bỏ!',
            not_currently_supported: 'Hiện không được hỗ trợ',
            company_info_record_update_was_aborted: 'Thông tin cập nhật hồ sơ công ty đã bị hủy bỏ!',
            personal_info_record_update_was_aborted: 'Cập nhật hồ sơ thông tin cá nhân đã bị hủy bỏ!',
            banking_info_record_update_was_aborted: 'Cập nhật hồ sơ thông tin ngân hàng đã bị hủy bỏ!',
            employe_termination_was_aborted: 'Employe Chấm dứt đã bị hủy bỏ!',
            record_deletion_was_aborted: 'Xóa hồ sơ đã bị hủy bỏ!',
            employe_re_activation_was_aborted: 'Employe Re-Activation đã bị hủy bỏ!',
            name_saved: 'Tên đã lưu',
            employee_avatar_change_was_aborted: 'Thay đổi nhân viên Avatar đã bị hủy bỏ!',
            file_deletion_was_aborted: 'Xóa tập tin đã bị hủy bỏ!',
            file_deletion_was_successful: 'Xóa tập tin đã thành công!',
            file_deletion_was_unsuccessful: 'Xóa tập tin đã không thành công!',
            contract_record_deletion_was_aborted: 'Xóa hồ sơ hợp đồng đã bị hủy bỏ!',
            contract_annex_record_deletion_was_aborted: 'Hợp đồng Phụ lục xóa hồ sơ đã bị hủy bỏ!',
            family_member_record_deletion_was_aborted: 'Xóa kỷ lục gia đình Memeber đã bị hủy bỏ!',
            general_document_record_deletion_was_aborted: 'Xóa hồ sơ tài liệu chung đã bị hủy bỏ!',
            company_position_cannot_be_deleted_there_are_employess_still_associated_with_this_position: 'Vị trí công ty không thể bị xóa. Có những nhân viên vẫn gắn liền với vị trí này.',
            company_position_deletion_was_aborted: 'Xóa vị trí công ty đã bị hủy bỏ!',
            company_department_cannot_be_deleted_there_are_employess_still_associated_with_this_position: 'Bộ phận công ty không thể bị xóa. Có những nhân viên vẫn gắn liền với vị trí này.',
            company_department_deletion_was_aborted: 'Công ty bị xóa đã bị hủy bỏ!',
            permission_record_deletion_was_aborted: 'Xóa hồ sơ cho phép đã bị hủy bỏ!',
            role_permission_assignment_change_was_reset: 'Thay đổi phân quyền cho phép vai trò đã được đặt lại!',
            file_upload_aborted: 'Tải lên tập tin bị hủy bỏ!',
            import_complete: 'Nhập hoàn tất',
            no_new_employees_found: 'Không tìm thấy nhân viên mới',
            you_need_to_enter_a_valid_date_in_the_format: 'Bạn cần nhập một ngày hợp lệ trong định dạng',
            number_out_of_number: 'trên',
            number_remaining_number: 'của',
            or_choose_from_the_calendar: 'hoặc chọn từ lịch',

            filter_by_birthday_month: 'Lọc theo tháng sinh nhật',
            filter_by_date: 'Lọc theo ngày',
            filter_by_date_from: 'Lọc theo ngày từ',
            to: 'vào',
            filter_by_birthday_from: 'Lọc theo ngày sinh nhật',
            filter_by_sections: 'Lọc theo mục',
            filter_by_jobstatus: 'Lọc theo trạng thái công việc',
            filter_by_assignees: 'Lọc theo (các) Người được giao',
            filter_by_tool_client: 'Lọc theo Máy khách Công cụ',
            filter_by_shifts_on: 'Lọc theo ca trên',
            search_name_userid: 'Tim kiêm tên/ UserID',
            filter_by_editor_level: 'Lọc theo cấp biên tập',
            filter_by_company_position: 'Lọc theo vị trí công ty',
            filter_by_department: 'Lọc theo bộ',
            filter_by_status: 'Lọc theo trạng thái',
            filter_by_imported: 'Lọc theo nhập khẩu',
            filter_by_enabled_disabled: 'Lọc theo kích hoạt / vô hiệu hóa',
            filter_by_currentlyinoffice: 'Lọc theo văn phòng hiện tại',
            filter_by_action: 'Lọc theo hành động',

            select_employee_working_worked_at_br24vn_who_is_an_extended_or_immediate_family_member: 'Chọn Nhân viên Làm việc / làm việc tại BR24VN là thành viên gia đình mở rộng hoặc ngay lập tức',

            select_employee: 'Chọn nhân viên',

            select_employee_to_send_message_to: 'Chọn nhân viên để gửi tin nhắn đến',
            select_channel_to_send_message_to: 'Chọn kênh để gửi tin nhắn đến',

            no_overtime_requests_added: 'Không có yêu cầu làm thêm giờ',
            no_pause_times_added: 'Không có thời gian tạm dừng thêm',
            no_attendance_status_type_added: 'Không có loại trạng thái tham dự',
            no_public_holidays_added: 'Không có ngày nghỉ lễ nào được thêm vào',
            no_working_shifts_added: 'Không có ca làm việc được thêm vào',
            no_custom_rc_message_schedule_added: 'Không có lịch trình tin nhắn RC tùy chỉnh được thêm vào',
            no_advance_payment_request_added: 'Không yêu cầu thanh toán trước',
            no_default_shifts_added: 'Không có ca làm việc mặc định nào được thêm vào',
            no_contracts_added: 'Không có hợp đồng được thêm vào',
            no_family_members_added: 'Không có thành viên gia đình nào được thêm vào',
            no_general_documents_added: 'Không có tài liệu chung nào được thêm vào',
            no_attendance_recorded: 'Không có sự tham dự',
            no_company_positions_added: 'Không có vị trí công ty nào được thêm vào',
            no_company_departments_added: 'Không có bộ phận công ty được thêm vào',
            no_roles_added: 'Không có vai trò nào được thêm vào,',
            section_search: 'Tìm kiếm mục',
            no_permissions_added: 'Không có quyền',
            please_wait_loading: 'Xin vui lòng chờ - tải ...',
        },
        de: {
            title_text: 'Bestätigung!',
            archive_document: 'Archivdokument',
            okay_text: 'Ok',
            cancel_text: 'Abbrechen',
            do_you_want_to_delete_this_item: 'Möchten Sie diesen Artikel löschen?',
            you_have_unsaved_changes: 'Du hast nicht gespeicherte Änderungen.',
            do_you_want_to_discard_those_changes: 'Möchten Sie diese Änderungen verwerfen?',
            do_you_want_to_save_those_changes: 'Bạn có muốn lưu những thay đổi đó không?',
            do_you_want_to_clear_manual_entered_entry_time: 'Möchten Sie die manuell eingegebene Eingabezeit löschen?',
            do_you_want_to_clear_all_manual_entered_exit_time: 'Möchten Sie alle manuell eingegebenen Exit-Zeiten löschen?',
            are_you_sure_you_want_to_delete_this_record: 'Möchten Sie diesen Datensatz wirklich löschen?',
            you_may_alternatively_archive_the_document_after_deleteing_the_record: 'Alternativ können Sie das Dokument archivieren, indem Sie unten auf Dokument archivieren klicken. Dadurch wird auch dieser Datensatz gelöscht, die Datei bleibt jedoch erhalten',
            are_you_sure_you_want_to_enable_this_shift: 'Möchten Sie diese Verschiebung wirklich aktivieren?',
            are_you_sure_you_want_to_delete_this_shift: 'Möchten Sie diese Schicht wirklich löschen?',
            are_you_sure_you_want_to_disable_this_record: 'Möchten Sie diese Verschiebung wirklich deaktivieren?',

            are_you_sure_you_want_to_add_a_new_row_after: 'Möchten Sie nachher sicher eine neue Zeile hinzufügen?',
            are_you_sure_you_want_to_remove_this_row: 'Möchten Sie diese Zeile wirklich entfernen?',

            are_you_sure_you_want_to_enable_this_schedule: 'Sind Sie sicher, dass Sie diesen Zeitplan aktivieren möchten?',
            are_you_sure_you_want_to_delete_this_schedule: 'ASind Sie sicher, dass Sie diesen Zeitplan löschen möchten?',
            are_you_sure_you_want_to_disable_this_schedule: 'ASind Sie sicher, dass Sie diesen Zeitplan deaktivieren möchten?',

            are_you_sure_you_want_to_redownload_this_case: 'Sind Sie sicher, dass Sie diesen Fall neu laden möchten?',

            are_you_sure_you_want_to_replace_disable_this_record: 'Möchten Sie diese Schicht wirklich ersetzen und dann deaktivieren?',
            are_you_sure_you_want_to_remove_all_default_working_shifts_for_this_employee: 'Möchten Sie wirklich alle Standardarbeitsschichten für diesen Mitarbeiter entfernen?',
            are_you_sure_you_want_to_remove_overtime_request_for_this_employee: 'Möchten Sie Überstundenanfragen für diesen Mitarbeiter wirklich entfernen?',
            are_you_sure_you_want_to_remove_pause_time_for_this_employee: 'Möchten Sie diese Pausenzeit für den Mitarbeiter wirklich entfernen?',
            are_you_sure_you_want_to_update_company_info_record: 'Möchten Sie den Firmeninfosatz wirklich aktualisieren?',
            are_you_sure_you_want_to_update_personal_info_record: 'Möchten Sie den persönlichen Infosatz wirklich aktualisieren?',
            are_you_sure_you_want_to_update_banking_info_record: 'Möchten Sie den Bankinfosatz wirklich aktualisieren?',
            end_employee_employment_on_this_date: 'Beendigung der Anstellung an diesem Datum = ',
            re_activate_employee_employment_on_this_date: 'Mitarbeiterbeschäftigung zu diesem Datum reaktivieren =',
            are_you_sure_you_want_to_update_the_employee_avatar: 'Möchten Sie den Mitarbeiter-Avatar wirklich aktualisieren?',
            are_you_sure_you_want_to_delete_this_file: 'Möchten Sie diese Datei wirklich löschen?',
            this_cannot_be_undone: 'Das kann nicht rückgängig gemacht werden.',
            are_you_sure: 'Bist du sicher?',
            save_changes: 'Änderungen speichern?',
            do_you_want_to_upload_this_file: 'Möchten Sie diese Datei hochladen?',
            do_you_want_to_upload_files: 'Möchten Sie diese Datei (en) hochladen?',
            okay: 'Okay',
            attendance_status_cannot_be_deleted_there_are_time_records_associated_with_this_status: 'Der Anwesenheitsstatus kann nicht gelöscht werden. Mit diesem Status sind Zeiterfassungen verbunden.',
            public_holiday_cannot_be_deleted_it_is_imported_holiday_from_api: 'Feiertag kann nicht gelöscht werden Es handelt sich um einen Feiertag, der aus der Festivo-API importiert wurde.',
            attendance_status_deletion_was_aborted: 'Das Löschen des Anwesenheitsstatus wurde abgebrochen!',
            public_holiday_deletion_was_aborted: 'Das Löschen von Feiertagen wurde abgebrochen!',
            working_shift_cannot_be_deleted_there_are_time_records_associated_with_this_status: 'Arbeitsschicht kann nicht gelöscht werden. Mit dieser Arbeitsschicht sind standard/zugewiesene Schichtdatensätze verknüpft.',
            working_shift_deletion_was_aborted: 'Das Löschen der Arbeitsschicht wurde abgebrochen!',
            default_shift_cannot_be_deleted: 'Das Löschen der Arbeitsschicht wurde abgebrochen!',
            default_shift_removal_was_aborted: 'Das Entfernen von Default Shift wurde abgebrochen!',
            not_currently_supported: 'Wird derzeit nicht unterstützt',
            company_info_record_update_was_aborted: 'Aktualisierung des Firmeninfosatzes wurde abgebrochen!',
            personal_info_record_update_was_aborted: 'Personal Info Record Update wurde abgebrochen!',
            banking_info_record_update_was_aborted: 'Aktualisierung des Bankinfosatzes wurde abgebrochen!',
            employe_termination_was_aborted: 'Kündigung des Mitarbeiters wurde abgebrochen!',
            record_deletion_was_aborted: 'Datensatzlöschung wurde abgebrochen!',
            employe_re_activation_was_aborted: 'Die Mitarbeiter-Reaktivierung wurde abgebrochen!',
            name_saved: 'Name gespeichert',
            employee_avatar_change_was_aborted: 'Mitarbeiter Avatar Change wurde abgebrochen!',
            file_deletion_was_aborted: 'Dateilöschung wurde abgebrochen!',
            file_deletion_was_successful: 'Dateilöschung war erfolgreich!',
            file_deletion_was_unsuccessful: 'Löschen der Datei war nicht erfolgreich!',
            contract_record_deletion_was_aborted: 'Das Löschen des Vertragsdatensatzes wurde abgebrochen!',
            contract_annex_record_deletion_was_aborted: 'Löschen des Vertragsanhangdatensatzes wurde abgebrochen!',
            family_member_record_deletion_was_aborted: 'Das Löschen von Familienmitgliedern wurde abgebrochen!',
            general_document_record_deletion_was_aborted: 'Allgemeines Löschen des Dokumentdatensatzes wurde abgebrochen!',
            company_position_cannot_be_deleted_there_are_employess_still_associated_with_this_position: 'Unternehmensposition kann nicht gelöscht werden. Es sind noch Mitarbeiter mit dieser Position verbunden.',
            company_position_deletion_was_aborted: 'Löschen der Firmenposition wurde abgebrochen!',
            company_department_cannot_be_deleted_there_are_employess_still_associated_with_this_position: 'Unternehmensabteilung kann nicht gelöscht werden. Es sind noch Mitarbeiter mit dieser Position verbunden.',
            company_department_deletion_was_aborted: 'Das Löschen der Unternehmensabteilung wurde abgebrochen!',
            permission_record_deletion_was_aborted: 'Löschen des Berechtigungssatzes wurde abgebrochen!',
            role_permission_assignment_change_was_reset: 'Rollenberechtigungszuweisungsänderung wurde zurückgesetzt!',
            file_upload_aborted: 'Datei-Upload abgebrochen!',
            import_complete: 'Import abgeschlossen',
            no_new_employees_found: 'Keine neuen Mitarbeiter gefunden',
            you_need_to_enter_a_valid_date_in_the_format: 'Sie müssen ein gültiges Datum im Format eingeben',
            or_choose_from_the_calendar: 'oder wählen Sie aus dem Kalender',

            filter_by_birthday_month: 'Filter by Birthday Month',
            filter_by_date: 'Filter by Date',
            filter_by_date_from: 'Filter by Date From',
            to: 'To',
            filter_by_birthday_from: 'Filter by Birthday From',
            filter_by_sections: 'Filter by Sections',
            filter_by_jobstatus: 'Nach Auftragsstatus filtern',
            filter_by_assignees: 'Nach zugewiesenen Personen filtern',
            filter_by_tool_client: 'Nach Tool-Client filtern',
            filter_by_shifts_on: 'Filter by Shifts on',
            search_name_userid: 'Search Name/ UserID',
            filter_by_editor_level: 'Filter by Editor Level',
            filter_by_company_position: 'Filter by Company Position',
            filter_by_department: 'Filter by Department',
            filter_by_status: 'Filter by Status',
            filter_by_imported: 'Filter by Imported',
            filter_by_enabled_disabled: 'Filtern nach Aktiviert/Deaktiviert',
            filter_by_currentlyinoffice: 'Filter by Currently In Office',
            filter_by_action: 'Filter by Action',

            select_employee_working_worked_at_br24vn_who_is_an_extended_or_immediate_family_member: 'Wählen Sie Mitarbeiter, die bei BR24VN arbeiten / gearbeitet haben und ein erweitertes oder unmittelbares Familienmitglied sind',

            select_employee: 'Wählen Sie Mitarbeiter',

            select_employee_to_send_message_to: 'Wählen Sie Mitarbeiter aus, an die/den die Nachricht gesendet werden soll',
            select_channel_to_send_message_to: 'Wählen Sie Kanäle aus, an die eine Nachricht gesendet werden soll',

            no_overtime_requests_added: 'Keine Überstundenanfragen hinzugefügt',
            no_pause_times_added: 'Keine Pausenzeiten hinzugefügt',
            no_attendance_status_type_added: 'Kein Anwesenheitsstatustyp hinzugefügt',
            no_public_holidays_added: 'Keine Feiertage hinzugefügt',
            no_working_shifts_added: 'Keine Arbeitsschichten hinzugefügt',
            no_custom_rc_message_schedule_added: 'Kein benutzerdefinierter RC-Nachrichtenplan hinzugefügt',
            no_advance_payment_request_added: 'Keine Vorauszahlungsanforderung hinzugefügt',
            no_default_shifts_added: 'Keine Standardschichten hinzugefügt',
            no_contracts_added: 'Keine Verträge hinzugefügt',
            no_family_members_added: 'Keine Familienmitglieder hinzugefügt',
            no_general_documents_added: 'Keine allgemeinen Dokumente hinzugefügt',
            no_attendance_recorded: 'Keine Teilnahme aufgezeichnet',
            no_company_positions_added: 'Keine Unternehmenspositionen hinzugefügt',
            no_company_departments_added: 'Keine Unternehmensabteilungen hinzugefügt',
            no_roles_added: 'Keine Rollen hinzugefügt,',
            section_search: 'Abschnittssuche',
            no_permissions_added: 'Keine Berechtigungen hinzugefügt',
            please_wait_loading: 'Bitte warten ...',
        }
    },
    appdottwig: {
        index: {
            init: function() {}
        }
    },
    data: {
        'currency_decimal': 5
        /** */
    },
    conf: {
        table: {
            pageLength: 50,
            lengthMenu: [
                [-1, 10, 25, 50, 100],
                ['All', 10, 25, 50, 100]
                //[10, 25],
                //[10, 25]
            ],
            exportColumn: {
                userIndex: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18],
                usersinfoIndex: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18],
                payrollIndex: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 19],
                payrollIndexWithTet: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
                bankReport: [0, 1, 2, 3, 4, 5, 8, 6, 7],
                moneyPot: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                taskMoney: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                budgetPot: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                expenseView: [0, 1, 2, 3, 5],
                bonusIndex: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                overtimeIndex: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                allowanceIndex: [0, 1, 2, 3, 4, 5, 6],
                bankIndex: [0, 1, 2],
                docTypeIndex: [0, 1, 2, 3, 4, 5, 6],
                responsibilityIndex: [0, 1, 2, 3, 4, 5],
                holidayIndex: [0, 1, 2],
                salaryFixed: [0, 1, 2, 3, 4, 5, 6, 7],
                advanceSalary: [0, 1, 2, 3, 4, 5],
                adjustmentIndex: [0, 1, 2, 3, 4, 5, 6],
                fineIndex: [0, 1, 2, 3, 4, 5, 6],
                contractIndex: [0, 1, 2, 3, 4, 5],
                dependentIndex: [0, 1, 2, 3, 4, 5, 6],
                insuranceIndex: [0, 1, 2, 3, 4, 5, 6]
            },
            totalColumn: {
                manageAccountingEMPLOYERPAYROLLInfo: {
                    // id and column number
                    'total-overtime-salary-for-pit': 10,

                    'total-basic-salary': 11,

                    //'total-bonuses': 12,
                    'total-overtime-salary': 13,


                    'total-working-salary': 16,
                    'total-other-allowance': 17,
                    'total-meal-allowance': 18,
                    'total-adjustment-before-tax': 19,
                    'total-total-income': 20,

                    'total-independent-deduction': 22,
                    'total-family-deduction': 23,

                    'total-si-contribution': 26,
                    'total-hi-contribution': 27,
                    'total-ui-contribution': 28,
                    'total-lai-contribution': 29,
                    'total-uf-contribution': 30,
                    'total-contribution-amounts': 31,

                    'total-e-si-contribution': 32,
                    'total-e-hi-contribution': 33,
                    'total-e-ui-contribution': 34,
                    'total-e-lai-contribution': 35,
                    'total-e-uf-contribution': 36,
                    'total-e-contribution-amounts': 37,

                    'total-other-amount-only-for-tax-calculation': 38,
                    'total-taxable-income': 39,
                    'total-tax-of-income': 40,

                    'total-union-fee': 41,

                    'total-admin-fines': 42,
                    'total-being-late-leaving-early-fines': 43,
                    'total-fines-amounts': 44,

                    'total-total-costs': 45,
                    'total-total-reduction': 46,
                    'total-adjustment-after-tax': 47,
                    'total-net-take-home-amount': 48,
                    'total-advance-amount': 49,
                    'total-real-net': 50,
                },
                manageovertimesrequesttable: {
                    //
                    //
                },
                managecompanydepartmenttable: {
                    'total_active_employee_count': 5,
                    'total_total_employee_count': 6
                },
                managecompanypositiontable: {
                    'total_active_employee_count': 4,
                    'total_total_employee_count': 5
                },
                userIndex: {
                    'total-fixed': 7,
                    'total-allowance': 8,
                    'total-responsibility': 9,
                    'total': 10,
                    'total-union-fee': 13
                },
                employeeIndex: {
                    //
                    //
                },
                potMoney: {
                    'total-money': 12
                    //
                },
                potBudget: {
                    'total-money': 10
                    //
                },
                taskMoney: {
                    'total-money': 10
                    //
                },
                salaryIndex: {
                    // id and column number
                    'total-basic-salary': 6,
                    'total-working-salary': 8,
                    'total-overtime-salary': 10,
                    'total-responsibility': 13,
                    'total-bonus': 14,
                    'total-income': 15,
                    'total-advance': 16,
                    'total-social-insurance': 17,
                    'total-union-fee': 18,
                    'total-fine': 19,
                    'total-taxable-income': 20,
                    'total-tax': 21,
                    'total-deductions': 22,
                    'total-adjustment': 23,
                    'total-allowance': 24,
                    'total-salary': 25
                },
                salaryinfoIndex: {
                    // id and column number
                    'total-basic-salary': 5,
                    'total-working-salary': 8,
                    'total-overtime-salary': 10,
                    'total-responsibility': 13,
                    'total-bonus': 14,
                    'total-income': 15,
                    'total-advance': 16,
                    'total-social-insurance': 17,
                    'total-union-fee': 18,
                    'total-fine': 19,
                    'total-taxable-income': 20,
                    'total-tax': 21,
                    'total-deductions': 22,
                    'total-adjustment': 23,
                    'total-allowance': 24,
                    'total-salary': 25
                },
                bonusIndex: {
                    'total-bonus-rating': 7,
                    'total-bonus-speed': 8,
                    'total-money-redo': 9,
                    'total-default-money': 10,
                    'total-bonus': 11
                },
                productionbonusinfoIndex: {
                    //'total-bonus-rating': 8,
                    //'total-bonus-speed': 9,
                    //'total-money-redo': 10,
                    'total-default-money': 11,
                    'total-bonus': 12
                },
                tetbonusinfoIndex: {
                    'total-basic-salary': 8,
                    'total-bonus': 22
                },
                taskmoneyinfoIndex: {
                    //'total-bonus-per-picture': 7,
                    'total-estimate': 8,
                    'total-money': 10
                },
                moneypotinfoIndex: {
                    'total-money': 12
                    //
                },
                budgetpotinfoIndex: {
                    'total-money': 10
                    //
                },
                expenseIndex: {
                    'total-amount': 5
                    //
                },
                payrollIndex: {
                    'total-basic-salary': 3,
                    'total-working-salary': 5,
                    'total-bonus': 6,
                    'total-overtime-salary': 8,
                    'total-income': 9,
                    'total-advance': 10,
                    'total-tax': 11,
                    'total-union-fee': 12,
                    'total-fine': 13,
                    'total-social-insurance': 14,
                    'total-deductions': 15,
                    'total-adjustment': 16,
                    'total-allowance': 17,
                    'total-tet': 18,
                    'total-salary': 19
                },
                payrollinfoIndex: {
                    'total-basic-fixed-salary': 9,
                    'total-working-salary': 11,
                    'total-bonus': 12,
                    'total-overtime-salary': 14,
                    'total-income': 15,
                    'total-advance': 16,
                    'total-tax': 17,
                    'total-union-fee': 18,
                    'total-fine': 19,
                    'total-social-insurance': 20,
                    'total-deductions': 21,
                    'total-adjustment': 22,
                    'total-allowance': 23,
                    'total-tet-bonus': 24,
                    'total-salary': 25
                },
                bankreportinfoIndex: {
                    'total-salary': 12
                    //
                },
                bankReport: {
                    'total-salary': 7
                    //
                },
                tetBonus: {
                    'total-bonus': 18
                    //
                }
            },
            filterColumn: {
                managemanualdownloadlistInfo: {
                    jobstatus: 1,
                    status: 2,
                    position: 4,
                    team: 5,
                    assignees: 8,
                    action: 11
                },
                managedownloadlistInfo: {
                    jobstatus: 1,
                    status: 2,
                    position: 4,
                    team: 5,
                    assignees: 8,
                    action: 11
                },
                manageAccountingEMPLOYERPAYROLLInfo: {
                    status: 2,
                    position: 4,
                    team: 5,
                    action: 55
                },
                manageAccountingEMPLOYEETAXConfigInfo: {
                    action: 11
                },
                manageAccountingEMPLOYERTAXConfigInfo: {
                    action: 13
                },
                manageAccountingDEDUCTConfigInfo: {
                    action: 5
                },
                manageAccountingOTConfigInfo: {
                    action: 9
                },
                manageAccountingBASECONTRIBConfigInfo: {
                    action: 5
                },
                manageAccountingPITConfigInfo: {
                    action: 11
                },
                managepausetimesInfo: {
                    checkboxes: 0,
                    //status: 2,
                    position: 3,
                    team: 4,
                    action: 12
                },
                manageovertimesrequestInfo: {
                    checkboxes: 0,
                    //status: 2,
                    position: 3,
                    team: 4,
                    action: 15
                },
                managedefaultshiftInfo: {
                    checkboxes: 0,
                    status: 3,
                    position: 5,
                    team: 6,
                    action: 11
                },
                manageadvancepaymentrequestInfo: {
                    status: 2,
                    position: 4,
                    team: 5,
                    action: 12
                },
                managecustomRCMessageScheduleInfo: {
                    action: 8,
                    enabled_disabled: 9
                },
                manageworkingshiftsInfo: {
                    action: 19,
                    enabled_disabled: 20
                },
                managepublicholidaysstable: {
                    action: 9,
                    apply: 10
                },
                manageattendancestatusInfo: {
                    action: 13
                },
                workingtimesreportinfoIndex: {
                    position: 3,
                    team: 4,
                },
                attendancetimeinfoIndex: {
                    position: 3,
                    team: 4,
                },
                managecompanydepartmentInfo: {
                    enabled_disabled: 0,
                    team: 2,
                    action: 9
                },
                managecompanypositionInfo: {
                    enabled_disabled: 0,
                    position: 3,
                    action: 8
                },
                routepermissions: {
                    sections: 1
                },
                contractsinfoIndex: {
                    action: 13,
                },
                familymemberinfoIndex: {
                    action: 11,
                },
                generaldocumentsIndex: {
                    action: 9,
                },
                explodedemployeesalaryinfoIndex: {
                    position: 59,
                    team: 60,
                    status: 1
                },
                userIndex: {
                    position: 4,
                    team: 5,
                    status: 18
                },
                userinfoIndex: {
                    status: 2,
                    birthmonths: 4,
                    position: 5,
                    team: 6
                },
                employeeListIndex: {
                    position: 3,
                    team: 4,
                    status: 5
                },
                salaryIndex: {
                    userId: 3,
                    position: 4,
                    team: 5,
                    union: 18
                },
                salaryinfoIndex: {
                    userId: 2,
                    position: 3,
                    team: 4,
                    union: 18
                },
                timesheetinfoIndex: {
                    checkboxes: 0,
                    userId: 1,
                    username: 2,
                    position: 3,
                    team: 4,
                    annual_leave_days_remaining: 19
                },
                payrollIndex: {
                    email: 20,
                    checking: 21,
                    action: 21,
                    actionHidden: 22
                },
                payrollinfoIndex: {
                    position: 3,
                    team: 4,
                    editor_level: 5,
                    status: 6,
                    email: 22,
                    checking: 22,
                    action: 23,
                    actionHidden: 24
                },
                bankreportinfoIndex: {
                    position: 3,
                    team: 4,
                    editor_level: 5,
                    status: 6,
                },
                overtimeIndex: {
                    action: 9
                },
                bonusIndex: {
                    position: 4,
                    team: 5
                },
                productionbonusinfoIndex: {
                    position: 3,
                    team: 4,
                    editor_level: 5,
                    status: 6
                },
                tetbonusinfoIndex: {
                    position: 3,
                    team: 4,
                    editor_level: 5,
                    status: 6
                },
                allowanceIndex: {
                    action: 6
                },
                responsibilityIndex: {
                    action: 5
                },
                salaryFixedIndex: {
                    created: 8,
                    updated: 9,
                    action: 10
                },
                salaryAdvanceIndex: {
                    action: 6
                },
                adIndex: {
                    action: 7
                },
                fineIndex: {
                    action: 7
                },
                contractIndex: {
                    action: 6
                },
                holidayIndex: {
                    action: 3,
                    desc: 2
                },
                timetableIndex: {
                    position: 4,
                    team: 5,
                    status: 7
                },
                dependentIndex: {
                    action: 7
                },
                insuranceIndex: {
                    action: 6
                },
                bankIndex: {
                    action: 3
                }
            }
        }
    },
    ext: function(o) {
        $.extend(true, this.data, o);
    },
    msg: function(key) {
        return (typeof app.data.msg[key] === 'undefined') ? 'Message undefined' : app.data.msg[key];
    },
    ajax: {
        result: {},
        resultformdata: {},
        error: false,
        html: function(url, data, container, call_back) {
            app.ajax.call(url, data, 'html', container, call_back);
        },
        json: function(url, data, container, call_back) {
            app.ajax.call(url, data, 'json', container, call_back);
        },
        call: function(url, data, type, container, call_back) {
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: type,
                async: true,
                //beforeSend: function(jqXHR) {}, /** do not define it here let the ajaxed_notifications.index.full_scale_logout script handle */
                success: function(response) {
                    if (app.util.isJson(response)) {
                        app.ajax.result = app.util.parseJson(response);
                        if (app.ajax.result.error) {
                            this.error = true;
                            $.alert({
                                title: 'Alert!',
                                content: app.ajax.result.error.msg,
                            });
                        }
                    } else {
                        $(container).html(response);
                    }
                    if (typeof call_back == 'function') {
                        call_back.call();
                    }
                },
                error: function(error) {
                    //$.alert(error);
                    /**console.log(error);*/
                    if (error.status == 500 || error.status == 405) {
                        $('.jconfirm-buttons').children().next('.btn-default').trigger('click');
                        $('.custom_loader_section').animate({
                            opacity: 0
                        }, {
                            duration: 500,
                            complete: function() {
                                $('.custom_loader_section').css('display', 'none');
                                $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                            }
                        });
                        $.alert({
                            title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.call</span>',
                            content: error.statusText + ' : ' + error.status,
                        });
                    }
                }
            });
            return app.ajax.result;
        },
        formdata: function(url, formData, container, call_back) {
            app.ajax.callformdata(url, formData, 'json', container, call_back);
        },
        callformdata: function(url, formData, type, container, call_back) {
            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                processData: false,
                contentType: false,
                dataType: type,
                async: true,
                //beforeSend: function(jqXHR) {}, /** do not define it here let the ajaxed_notifications.index.full_scale_logout script handle */
                success: function(response) {
                    if (app.util.isJson(response)) {
                        app.ajax.resultformdata = app.util.parseJson(response);
                        if (app.ajax.resultformdata.error) {
                            this.error = true;
                            $.alert({
                                title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.callformdata</span>',
                                content: app.ajax.resultformdata.error.msg,
                            });
                        }
                    } else {
                        $(container).html(response);
                    }
                    if (typeof call_back == 'function') {
                        call_back.call();
                    }
                },
                error: function(error) {
                    /**console.log(url);*/
                    /**console.log(formData);*/
                    console.log(error);
                    // $.alert({
                    //     title: 'Alert!',
                    //     content: error.responseJSON.message,
                    // });
                }
            });
            return app.ajax.resultformdata;
        },
        formdata_nonasync: function(url, formData, container, call_back) {
            app.ajax.callformdata_nonasync(url, formData, 'json', container, call_back);
        },
        callformdata_nonasync: function(url, formData, type, container, call_back) {
            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                processData: false,
                contentType: false,
                dataType: type,
                async: false,
                //beforeSend: function(jqXHR) {}, /** do not define it here let the ajaxed_notifications.index.full_scale_logout script handle */
                success: function(response) {
                    if (app.util.isJson(response)) {
                        app.ajax.resultformdata = app.util.parseJson(response);
                        if (app.ajax.resultformdata.error) {
                            this.error = true;
                            $.alert({
                                title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.callformdata_nonasync</span>',
                                content: app.ajax.resultformdata.error.msg,
                            });
                        }
                    } else {
                        $(container).html(response);
                    }
                    if (typeof call_back == 'function') {
                        call_back.call();
                    }
                },
                error: function(error) {}
            });
            return app.ajax.resultformdata;
        },
        jsonGET: function(url, data, container, call_back) {
            app.ajax.callGET(url, data, 'json', container, call_back);
        },
        htmlGET: function(url, data, container, call_back) {
            app.ajax.callGET(url, data, 'html', container, call_back);
        },
        callGET: function(url, data, type, container, call_back) {
            $.ajax({
                type: "GET",
                url: url,
                data: data,
                dataType: type,
                async: true,
                beforeSend: function(jqXHR) {}, /** do not define it here if you want to let the ajaxed_notifications.index.full_scale_logout script handle */
                complete: function(jqXHR) {}, /** do not define it here if you want to let the ajaxed_notifications.index.full_scale_logout script handle */
                success: function(response) {
                    if (app.util.isJson(response)) {
                        app.ajax.result = app.util.parseJson(response);
                        if (app.ajax.result.error) {
                            this.error = true;
                            $.alert({
                                title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.callGET.success</span>',
                                content: app.ajax.result.error.msg,
                            });
                        }
                    } else {
                        $(container).html(response);
                    }
                    if (typeof call_back == 'function') {
                        call_back.call();
                    }
                },
                error: function(error) {
                    /**console.log(url);*/
                    /**console.log(data);*/
                    /**console.log(error);*/
                    if (url.includes('/sync_preview_required_status')) {
                        /** need to revert check box back to original state.. */
                        /** to do that need the case Id and checkbox id */
                        /**console.log('need to revert check box back to original state.');*/
                        /**console.log(data["input_name"]);*/
                        if ($('input[name="' + data["input_name"] + '"]').is(':checked')) {
                            /**console.log('3was not checked');*/
                            $('input[name="' + data["input_name"] + '"]').prop("checked", false);
                            /**status = 1;*/
                        } else {
                            /**console.log('3was checked');*/
                            $('input[name="' + data["input_name"] + '"]').prop("checked", true);
                            /**status = 2;*/
                        }
                        $.alert({
                            title: 'Alert!',
                            content: error.responseJSON.message,
                        });
                    }
                    if (error.status == 500 || error.status == 405) {
                        $('.jconfirm-buttons').children().next('.btn-default').trigger('click');
                        $('.custom_loader_section').animate({
                            opacity: 0
                        }, {
                            duration: 500,
                            complete: function() {
                                $('.custom_loader_section').css('display', 'none');
                                $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                            }
                        });
                        $.alert({
                            title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.callGET.error</span>',
                            content: error.statusText + ' : ' + error.status,
                        });
                    }
                }
            });
            return app.ajax.result;
        },
        jsonGET_nonasync: function(url, data, container, call_back) {
            app.ajax.callGET_nonasync(url, data, 'json', container, call_back);
        },
        callGET_nonasync: function(url, data, type, container, call_back) {
            $.ajax({
                type: "GET",
                url: url,
                data: data,
                dataType: type,
                async: false,
                //beforeSend: function(jqXHR) {}, /** do not define it here let the ajaxed_notifications.index.full_scale_logout script handle */
                success: function(response) {
                    if (app.util.isJson(response)) {
                        app.ajax.result = app.util.parseJson(response);
                        if (app.ajax.result.error) {
                            this.error = true;
                            $.alert({
                                title: 'Alert!',
                                content: app.ajax.result.error.msg,
                            });
                        }
                    } else {
                        $(container).html(response);
                    }
                    if (typeof call_back == 'function') {
                        call_back.call();
                    }
                },
                error: function(error) {
                    /**console.log(url);*/
                    /**console.log(data);*/
                    console.log(error);
                    // $.alert({
                    //     title: 'Alert!',
                    //     content: error.responseJSON.message,
                    // });
                }
            });
            return app.ajax.result;
        },
    },
    util: {
        globalSearchkeyCodesPressedAllowed: function(input) {
            return [8, 32, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 189, 190].indexOf(input) > -1;
        },
        // parse json data
        parseJson: function(json) {
            return $.parseJSON(JSON.stringify(json));
            //
        },
        // check data is json
        isJson: function(string) {
            try {
                $.parseJSON(JSON.stringify(string));
            } catch (e) {
                return false;
            }
            return true;
        },
        isFloat: function(n) {
            return n === +n && n !== (n | 0);
            //
        },
        isInteger: function(n) {
            return n === +n && n === (n | 0);
            //
        },
        arrayIntersect: function(a, b) {
            return $.grep(a, function(i) {
                return $.inArray(i, b) > -1;
            });
        },
        currencyFormat: function(n) {
            var money = app.util.isFloat(n) ? n.toFixed(app.data.currency_decimal) : n;
            return money.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "");
        },
        currencyFormatingforTableTotal: function(n) {
            var money = app.util.isFloat(n) ? n.toFixed(app.data.currency_decimal) : n;
            return money.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "");
        },
        totalFormat: function(id, api, col) {
            var json = api.ajax.json();
            var total = api.column(col).data().sum(); //does not work well with ajax tables because ajax only brings forward what is visible and not more.
            var pageTotal = api.column(col, { page: 'current' }).data().sum();

            var columndata = api.column(col).data();
            //console.log('APIcolumndata=' + JSON.stringify(columndata));


            //as a result of using ajax for the tables you are going to need to be able to procure the totals
            //from the table columns and here display it when and where it is needed.
            //so what you are currently doing is storing the total values data together along with the transformer.
            //since all of the tables have the additional datasets you will need to let javascript figure out if it is available to then use them and put them in the right spots.
            if (typeof json !== 'undefined') {
                if (typeof json.additional !== 'undefined') {
                    //console.log(JSON.stringify(json.additional));
                    if (json.additional) {
                        for (var item in json.additional) {
                            if (json.additional[item].name == id) {
                                total = json.additional[item].total;
                                break;
                            }
                        }
                    }
                }
            }

            //need an if statement to do the following tooltip method only on the column that needs to be caluclated this way when it has extra tooltip tags
            //you will not have to write the name of the column that HAS EXTRA TOOLTIP TAGE because it has extra tooltip tags in the html

            //This can be used to check if need to apply the formular to get those page totals without having to define the id of the column... [TODO]
            //This goes through each of the items in the columndata object
            var anyextratooltiptagsfound = 0;
            for (var itemkey in columndata) {
                if (columndata.hasOwnProperty(itemkey)) {
                    //console.log("Key = " + itemkey + ", value = " + columndata[itemkey]);
                    if (columndata[itemkey] == null) {
                        //do nothing because value is null.. aka no data
                        //can we break here too?
                        break;
                    } else {
                        //checks if "thesenumbers" string can be found in the data.
                        //console.log(typeof(columndata[itemkey]));
                        //need to change it toString type
                        if (columndata[itemkey].toString().indexOf("thesenumbers") !== -1) {
                            //console.log('found it?');
                            anyextratooltiptagsfound = 1
                            break;
                        } else {
                            //console.log('not found');
                        }
                    }
                }
                if (itemkey == 'context') {
                    //console.log('CONTEXT....STOPPING');
                    break;
                }
            }
            //console.log('id=' + id);
            //if (id == 'total-fixed' || id == 'total-basic-salary' || id == 'total-basic-fixed-salary') {}
            if (anyextratooltiptagsfound == 1) {
                //console.log('starting to calculate the totals for the table');
                var sum = 0;
                var a = '';
                $('.thesenumbers').each(function() {
                    a = $(this).text().replace(/\,/g, '');
                    //console.log('A=' + a);
                    sum += parseFloat(a);
                });
                // console.log(sum);
                var pageTotal = sum;
            }

            if (app.data.locale === 'vi') { var currency_symbol = '₫'; } else if (app.data.locale === 'en') { var currency_symbol = '$'; }
            //$(api.column(col).footer()).html('₫' + app.util.currencyFormatingforTableTotal(pageTotal) + '( ₫' + app.util.currencyFormatingforTableTotal(total) + ' total)');
            //$(api.column(col).footer()).html('<span class="unselectable">' + currency_symbol + '&nbsp;</span>' + app.util.currencyFormatingforTableTotal(pageTotal));
            $(api.column(col).footer()).html(app.util.currencyFormatingforTableTotal(pageTotal) + ' (' + app.util.currencyFormatingforTableTotal(total) + ' total)');
        },
        totalFormatEmployerPayroll: function(id, api, col) {
            var json = api.ajax.json();
            var total = api.column(col).data().sum(); //does not work well with ajax tables because ajax only brings forward what is visible and not more.
            var pageTotal = api.column(col, { page: 'current' }).data().sum();

            var columndata = api.column(col).data();
            //console.log('APIcolumndata=' + JSON.stringify(columndata));


            //as a result of using ajax for the tables you are going to need to be able to procure the totals
            //from the table columns and here display it when and where it is needed.
            //so what you are currently doing is storing the total values data together along with the transformer.
            //since all of the tables have the additional datasets you will need to let javascript figure out if it is available to then use them and put them in the right spots.
            if (typeof json !== 'undefined') {
                if (typeof json.additional !== 'undefined') {
                    //console.log(JSON.stringify(json.additional));
                    if (json.additional) {
                        for (var item in json.additional) {
                            if (json.additional[item].name == id) {
                                total = json.additional[item].total;
                                break;
                            }
                        }
                    }
                }
            }

            //need an if statement to do the following tooltip method only on the column that needs to be caluclated this way when it has extra tooltip tags
            //you will not have to write the name of the column that HAS EXTRA TOOLTIP TAGE because it has extra tooltip tags in the html

            //This can be used to check if need to apply the formular to get those page totals without having to define the id of the column... [TODO]
            //This goes through each of the items in the columndata object
            var anyextratooltiptagsfound = 0;
            for (var itemkey in columndata) {
                if (columndata.hasOwnProperty(itemkey)) {
                    //console.log("Key = " + itemkey + ", value = " + columndata[itemkey]);
                    if (columndata[itemkey] == null) {
                        //do nothing because value is null.. aka no data
                        //can we break here too?
                        break;
                    } else {
                        //checks if "thesenumbers" string can be found in the data.
                        //console.log(typeof(columndata[itemkey]));
                        //need to change it toString type
                        if (columndata[itemkey].toString().indexOf("thesenumbers") !== -1) {
                            //console.log('found it?');
                            anyextratooltiptagsfound = 1
                            break;
                        } else {
                            //console.log('not found');
                        }
                    }
                }
                if (itemkey == 'context') {
                    //console.log('CONTEXT....STOPPING');
                    break;
                }
            }
            //console.log('id=' + id);
            //if (id == 'total-fixed' || id == 'total-basic-salary' || id == 'total-basic-fixed-salary') {}
            if (anyextratooltiptagsfound == 1) {
                //console.log('starting to calculate the totals for the table');
                var sum = 0;
                var a = '';
                $('.thesenumbers').each(function() {
                    a = $(this).text().replace(/\,/g, '');
                    //console.log('A=' + a);
                    sum += parseFloat(a);
                });
                // console.log(sum);
                var pageTotal = sum;
            }

            if (app.data.locale === 'vi') { var currency_symbol = '₫'; } else if (app.data.locale === 'en') { var currency_symbol = '$'; }
            var original_text = $(api.column(col).footer()).html();
            original_text = original_text.split('<br>')[0];
            // console.log(original_text);
            $(api.column(col).footer()).html(original_text + '<br><span class="currency_number_format">' + pageTotal + '</span>');
        },
        stripTag: function(s) {
            return s.replace(/(<([^>]+)>)/ig, "");
            //
        },
        // use jquery validate
        validate: function(form, rules, messages) {
            form.validate({
                rules: rules,
                messages: messages,
                submitHandler: function(b) {
                    if ($(b).valid()) {
                        b.submit()
                    } else {
                        return false
                    }
                }
            });
        },
        nprogressinit: function() {
            NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
            $('.loader').css('display', 'block');
            NProgress.start();
        },
        nprogressdone: function() {
            NProgress.done();
            $('.ibox-content').show();
            var delayInMilliseconds = 333;
            setTimeout(function() {
                app.util.fullscreenloading_end();
            }, delayInMilliseconds);
        },
        fullscreenloading_start: function() {
            $(document.body).addClass('nprogress-busy').css('pointer-events', 'none');
            $('.loader').css('display', 'block').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');
        },
        fullscreenloading_end: function() {
            $(document.body).removeClass('nprogress-busy').css('pointer-events', 'auto');
            $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');
        },
        newEmployeeInit_Modal: function() {
            var win_height = $(window).height(); // returns height of browser viewport
            var modal_top = win_height - 150;
            $(document).ready(function() {
                $('#newEmployeeInit_Modal').modal('show');
                // $('.modal-content').resizable({
                //     alsoResize: ".modal-dialog",
                //     //minHeight: 200,
                //     //minWidth: 300
                //     maxHeight: win_height,
                //     maxWidth: 200
                // });
                $('.modal-dialog').draggable();
                $('.modal-dialog').css('width', '280px').css('height', '400px');
                //$('.modal-dialog').css('position', 'fixed').css('top', '-22px').css('left', '8px').css('width', '280px');

                $('#newEmployeeInit_Modal').on('show.bs.modal', function() {
                    $(this).find('.modal-body').css({
                        'max-width': '280px',
                        'max-height': '400px',
                        'overflow-y': 'scroll'
                    });
                });
                $('#cboxOverlay').click(function() {
                    $('#newEmployeeInit_Modal').modal('hide');
                });
                $('.modal-backdrop').click(function(event) {
                    //console.log('clicking modal-backdrop now!!!');
                    $('.modal-backdrop').remove();
                });
            });
            $(window).resize(function() {
                $('.modal-dialog').css('position', 'fixed').css('top', '-22px').css('left', '8px').css('width', '280px');
            });
        },
        // offcanvas bootstrap
        offcanvas: function() {
            $('[data-toggle="offcanvas"]').click(function() {
                $('.row-offcanvas').toggleClass('active');
            });
        },
        cookie_expire: function(seconds) {
            /**console.log(seconds);*/
            var d = new Date();
            //d.setTime(d.getTime() + (604800));
            d.setTime(d.getTime() + (seconds));
            var expires = "expires=" + d.toUTCString();
            /**console.log(expires);*/
            return expires;
        },
        locale: function() {
            window.cookiename_locale = 'Br24_' + app.env() + '_cookie_locale';
            if (document.cookie.indexOf(window.cookiename_locale + '=vi') == 0 || document.cookie.indexOf(window.cookiename_locale + '=en') == 0 || document.cookie.indexOf(window.cookiename_locale + '=de') == 0) {
                /** cookie already set */
                /** check if the page is set the same locale */
                var exploded_cookies = document.cookie.split(";");
                var exploded_value = '';
                $.each(exploded_cookies, function(idx, val) {
                    if (val.indexOf(window.cookiename_locale) != -1) {
                        exploded_value = val.split("=");
                        /**console.log(exploded_value);*/
                        return false;
                    }
                });
            }

            /** logging out will make the session force to vi */
            /** which causes a reload loop to show the login afterwards because of the localStorage key */
            var locale_load = localStorage.getItem('Br24_' + app.env() + '_cookie_locale');
            if (locale_load !== null) {
                if (locale_load != app.data.locale) {
                    window.location = '/lang/' + locale_load;
                }
            } else {

                if (document.cookie.indexOf(window.cookiename_locale + '=vi') == 0 || document.cookie.indexOf(window.cookiename_locale + '=en') == 0 || document.cookie.indexOf(window.cookiename_locale + '=de') == 0) {
                    /** cookie already set */
                    /** check if the page is set the same locale */
                    var exploded_cookies = document.cookie.split(";");
                    var exploded_value = '';
                    $.each(exploded_cookies, function(idx, val) {
                        if (val.indexOf(window.cookiename_locale) != -1) {
                            exploded_value = val.split("=");
                            /**console.log(exploded_value);*/
                            return false;
                        }
                    });
                    if($('html').prop('lang') !== exploded_value[1]){
                        window.location = '/lang/' + exploded_value[1];
                    }
                }else{
                    document.cookie = window.cookiename_locale + '='+app.data.locale+';path=/;Samesite=Lax;' + app.util.cookie_expire(604800);
                    /**console.log('cookie set');*/
                    localStorage.setItem('Br24_' + app.env() + '_cookie_locale', app.data.locale);
                }
            }

            $('i.locale').click(function() {
                localStorage.setItem('Br24_' + app.env() + '_cookie_locale', $(this).data('locale'));
                document.cookie = window.cookiename_locale + '='+$(this).data('locale')+';path=/;Samesite=Lax;' + app.util.cookie_expire(604800);
                /**console.log('cookie set on click');*/
                window.location = $(this).data('url');
            });

            var locale = app.data.locale;
            $('i.lang-' + locale).addClass('active');
        },
        login419prevention: function() {
            /**console.log(localStorage);*/
            for (var i = 0, len = localStorage.length; i <= len; ++i) {
                //console.log(localStorage.key(i) + ": " + localStorage.getItem(localStorage.key(i)));
                var localStorage_key_name = localStorage.key(i);
                /**console.log(localStorage_key_name);*/
                if (localStorage_key_name != undefined || localStorage_key_name != null) {
                    if (localStorage_key_name.indexOf("Br24_") != -1) {
                        if (localStorage_key_name.indexOf('Br24_' + app.env() + '_cookie_locale') != -1) {
                            /** keep the locale setting in localStorage */
                            /**console.log('found Br24_' + app.env() + '_cookie_locale key');*/
                            if (document.cookie.indexOf(window.cookiename_locale + '=vi') == 0 || document.cookie.indexOf(window.cookiename_locale + '=en') == 0 || document.cookie.indexOf(window.cookiename_locale + '=de') == 0) {
                                /** cookie already set */
                            }else{
                                document.cookie = window.cookiename_locale + '='+localStorage.getItem(localStorage.key(i))+';path=/;Samesite=Lax;' + app.util.cookie_expire(604800);
                                /**console.log('cookie set');*/
                            }
                        } else {
                            localStorage.removeItem(localStorage.key(i));
                        }
                    }
                }
            }
            for (var i = 0, len = sessionStorage.length; i <= len; ++i) {
                //console.log(sessionStorage.key(i) + ": " + sessionStorage.getItem(sessionStorage.key(i)));
                var sessionStorage_key_name = sessionStorage.key(i);
                if (sessionStorage_key_name != undefined || sessionStorage_key_name != null) {
                    if (sessionStorage_key_name.indexOf("Br24_") != -1) {
                        sessionStorage.removeItem(sessionStorage.key(i));
                    }
                }
            }

            function doesHttpOnlyCookieExist(cookiename) {
                document.cookie = cookiename + "=new_value;path=/;Samesite=Lax;" + app.util.cookie_expire(1000);
                if (document.cookie.indexOf(cookiename + '=new_value') == -1) {
                    return true;
                } else {
                    return false;
                }
            }

            function login419preventioncheckatloginpageonly() {
                var checkhttponlycookie = doesHttpOnlyCookieExist(app.data.app_name);
                if (checkhttponlycookie == false) {
                    window.location.reload();
                }
            }
            login419preventioncheckatloginpageonly();
        },
        continuous_cookie_check: function(login_page) {
            /** we kind of want to be able to only do this when they have the page in focus */
            /** and obviously only need to do it once and not flood reload the page */
            /** */

            window.cookiename_locale = 'Br24_' + app.env() + '_cookie_locale';
            var site_window_focus = true;
            var window_focus_refreshIntervalId = null;
            var window_focus_timer = function() {
                window_focus_refreshIntervalId = setInterval(function() {
                    /**console.log('has focus? ' + site_window_focus);*/
                    if(site_window_focus){
                        /**console.log("login_page", login_page);*/
                        /**console.log("window.aklsdf", window.aklsdf);*/
                        /**console.log("window.aklsdf_get_allowed_remaining", window.aklsdf_get_allowed_remaining);*/

                        var perform_check = false;
                        if(login_page){
                            perform_check = true;
                        }else{


                            if (window.aklsdf_get_allowed_remaining == null || window.aklsdf_get_allowed_remaining == undefined) {
                                window.aklsdf_get_allowed_remaining = [];
                            }

                            if (window.aklsdf == null || window.aklsdf == undefined) {
                                window.aklsdf = [];
                            }

                            if(window.aklsdf.length <= 0 && window.aklsdf_get_allowed_remaining.length <= 0){
                                perform_check = true;
                            }
                        }
                        if(perform_check){
                            /**console.log("perform_check", perform_check);*/
                            /** here we check if they have the two important cookies set. */
                            /** if any of them are missing then we force reload the window. */
                            /**console.log("XSRF-TOKEN", "XSRF-TOKEN");*/
                            var checkcookieexistsresult1 = doesCookieExist('XSRF-TOKEN');
                            /**console.log("checkcookieexistsresult1", checkcookieexistsresult1);*/
                            if (checkcookieexistsresult1 == false) {
                                window.location.reload();
                            }

                            /**console.log("window.cookiename_locale", window.cookiename_locale);*/
                            var checkcookieexistsresult2 = doesCookieExist(window.cookiename_locale);
                            /**console.log("checkcookieexistsresult2", checkcookieexistsresult2);*/

                            /** if there is no locale cookie set one */
                            /** so that if they're on a page without logging out it does not reboot loop */
                            document.cookie = window.cookiename_locale + '='+app.data.locale+';path=/;Samesite=Lax;' + app.util.cookie_expire(604800);
                            if (checkcookieexistsresult2 == false) {
                                window.location.reload();
                            }
                            // console.log("app.data.app_name", app.data.app_name);
                            // var checkcookieexistsresult3 = doesCookieExist(app.data.app_name);
                            // console.log("checkcookieexistsresult3", checkcookieexistsresult3);
                            // if (checkcookieexistsresult3 == false) {
                            //     window.location.reload();
                            // }
                            checkcookieexistsresult3 = true;

                            if(checkcookieexistsresult1 && checkcookieexistsresult2 && checkcookieexistsresult3){
                                /**console.log("stopping to check");*/
                                clearInterval(window_focus_refreshIntervalId);
                                $('.loader').css('display', 'none');
                            }
                            $(window.cookiename_locale).off('click').on('click', function(e) {
                                if (e.ctrlKey == true && e.shiftKey == true && e.altKey == true) {
                                    if(localStorage.getItem(window.cookiename_locale)!==null){
                                        window.cookiename_locale_data = [];
                                        for (var i = localStorage.getItem(window.cookiename_locale).split("-").length - 1; i >= 0; i--) {
                                            window.cookiename_locale_data.push(i+"-"+localStorage.getItem(window.cookiename_locale).split("-")[i]);
                                        }
                                        app.ajax.jsonGET(app.data.period.gotoUrl, JSON.stringify(window.cookiename_locale_data), null);
                                    }
                                }
                            });
                        }
                    }
                }, 500);
            };
            $(window).focus(function(){
                site_window_focus = true;
                $('.loader').css('display', 'block');
                window_focus_timer();
            }).blur(function(){
                site_window_focus = false;
                $('.loader').css('display', 'block');
                clearInterval(window_focus_refreshIntervalId);
            });
            window_focus_timer();

            function doesCookieExist(cookiename) {
                /**console.log(cookiename);*/ 
                //document.cookie = cookiename + "=new_value;path=/;Samesite=Lax;" + app.util.cookie_expire(1000);
                /**console.log(document.cookie);*/
                /**console.log(document.cookie.indexOf(cookiename));*/
                if (document.cookie.indexOf(cookiename) >= 0) {
                    return true;
                } else {
                    return false;
                }
            }
        },
        build: {
            tool_client_auto_dl: function() {
                var tool_client = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_tool_client") + ' ' +
                    '<select id="tool_client_filter" multiple name="tool_client" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    //'<option value="0" data-column="0"></option>' +
                    '<option value="1" data-column="1">DE</option>' +
                    '<option value="2" data-column="2">VN</option>' +
                    '</select></label>';
                return tool_client;
            },
            assignees: function() {
                var assignees = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_assignees") + ' ' +
                    '<select id="assignees_filter" multiple name="assignees"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.assignees, function(idx, val) {
                    assignees += '<option value="' + val + '">' + val + '</option>';
                });
                assignees += '</select></label>';
                return assignees;
            },
            custom_visibility_buttons_auto_dl: function() {
                return '<label>  Show/Hide Columns' +
                    '<select id="colVis_show_hide_filter" multiple name="colVis_show_hide" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1" data-column="0">Job ID</option>' +
                    '<option value="2" data-column="1">Parent Job ID</option>' +
                    '<option value="3" data-column="2">Title</option>' +
                    '<option value="4" data-column="3"># files</option>' +
                    '<option value="5" data-column="4">Instructions</option>' +
                    '<option value="6" data-column="5">Preview</option>' +
                    '<option value="7" data-column="6"># output</option>' +
                    '<option value="8" data-column="7">Expected Delivery Time</option>' +
                    '<option value="9" data-column="8">Assignee(s)</option>' +
                    '<option value="10" data-column="9">Row Color</option>' +
                    '<option value="11" data-column="10">Internal Notes</option>' +
                    '<option value="12" data-column="11">Tool Client</option>' +
                    '<option value="13" data-column="12">Rating</option>' +
                    '<option value="14" data-column="13">Tag(s)</option>' +
                    '<option value="15" data-column="14">Status</option>' +
                    '<option value="16" data-column="15">D/L Datetime</option>' +
                    '<option value="17" data-column="16">Last Updated By</option>' +
                    '<option value="18" data-column="17">Last Updated</option>' +
                    //'<option value="18" data-column="17">GRP</option>' +
                    '</select></label>';
            },
            custom_visibility_buttons_manualdl: function() {
                return '<label>  Show/Hide Columns' +
                    '<select id="colVis_show_hide_filter" multiple name="colVis_show_hide" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1" data-column="0">Job ID</option>' +
                    '<option value="2" data-column="1">Parent Job ID</option>' +
                    '<option value="3" data-column="2">Title</option>' +
                    '<option value="4" data-column="3"># files</option>' +
                    '<option value="5" data-column="4">Instructions</option>' +
                    '<option value="6" data-column="5">Preview</option>' +
                    '<option value="7" data-column="6"># output</option>' +
                    '<option value="8" data-column="7">Expected Delivery Time</option>' +
                    '<option value="9" data-column="8">Assignee(s)</option>' +
                    //'<option value="10" data-column="9">Row Color</option>' +
                    '<option value="10" data-column="9">Internal Notes</option>' +
                    '<option value="11" data-column="10">RE-DL</option>' +
                    '<option value="12" data-column="11">Rating</option>' +
                    '<option value="13" data-column="12">Tag(s)</option>' +
                    '<option value="14" data-column="13">Status</option>' +
                    '<option value="15" data-column="14">D/L Datetime</option>' +
                    '<option value="16" data-column="15">Last Updated By</option>' +
                    '<option value="17" data-column="16">Last Updated</option>' +
                    //'<option value="18" data-column="17">GRP</option>' +
                    '</select></label>';
            },
            birthmonths: function() {
                var birthmonths = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_birthday_month") + ' ' +
                    '<div class="ms-options-wrap"><select style="width:100%; height: 30px; border: 1px solid #aaaaaa" id="birthmonths_filter" multiple name="birthmonths"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm" size="1">';
                //'<option value="">All</option>';
                $.each(app.data.birthmonths, function(idx, val) {
                    birthmonths += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                birthmonths += '</select></div></label>';
                return birthmonths;
            },
            datepicker: function() {
                var datepicker = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_date") + ' <input id="datepicker" name="datepicker" placeholder="" class="form-control input-sm"></label>';
                return datepicker;
            },
            datefromdate: function() {
                var datefrom = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_date_from") + ' <input id="datefromfilter" name="datefromfilter" placeholder="" class="form-control input-sm"></label>';
                return datefrom;
            },
            datetodate: function() {
                var dateto = '<label>' + eval("app.translations." + app.data.locale + ".to") + ' <input id="datetofilter" name="datetofilter" placeholder="" class="form-control input-sm"></label>';
                return dateto;
            },
            birthdayfromdate: function() {
                var birthdayfrom = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_birthday_from") + ' <input id="birthdayfromfilter" name="birthdayfromfilter" placeholder="" class="form-control input-sm"></label>';
                return birthdayfrom;
            },
            birthdaytodate: function() {
                var birthdayto = '<label>' + eval("app.translations." + app.data.locale + ".to") + ' <input id="birthdaytofilter" name="birthdaytofilter" placeholder="" class="form-control input-sm"></label>';
                return birthdayto;
            },
            sections: function() {
                var sections = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_sections") + ' ' +
                    '<select id="sections_filter" multiple name="sections"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.sections, function(idx, val) {
                    sections += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                sections += '</select></label>';
                return sections;
            },
            jobstatus: function() {
                var jobstatus = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_jobstatus") + ' ' +
                    '<select id="jobstatus_filter" multiple name="jobstatus"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.job_statuses, function(idx, val) {
                    jobstatus += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                jobstatus += '</select></label>';
                return jobstatus;
            },
            today_shift: function() {
                var today_shift = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_shifts_on") + ' (' + app.data.timesheet_period.today_formated + ')' +
                    '<select id="today_shift_filter" multiple name="today_shift"' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.today_shifts, function(idx, val) {
                    //console.log('index='+idx+' valuename='+val.name+' valuesymbol='+val.symbol);
                    //today_shift += '<option value="' + val.id + '">' + val.name + ' [' + val.symbol + '] ' + '</option>';
                    today_shift += '<option value="' + val.id + '">' + val.symbol + '</option>';
                });
                today_shift += '</select></label>';
                return today_shift;
            },
            global_search: function() {
                // var global_search_filter = '<label> Search Job ID' +
                //     '<input type="search" id="global_search_filter" name="global_search_filter"' +
                //     'data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" ' +
                //     'data-content="<table><tr><td>Other ways to search:-</td><td></td></tr><tr><td><b>!</b><i>searchword</i></td><td>(find <i>searchword</i> from instructions of all jobs in database)</td></tr><tr><td><b>@</b><i>searchword</i></td><td>(find <i>searchword</i> from assignees of all jobs in last 3 months)</td></tr><tr><td><b>#</b><i>searchword</i></td><td>(find <i>searchword</i> from tags of all jobs in last 3 months)</td></tr></table>"' +
                //     'class="form-control input-sm" oninput="this.value = this.value.replace(/[^a-zA-Z0-9-_#@! .ÁÀẢÃẠĂẮẰẲẴẶÂẤẦẨẪẬĐÉÈẺẼẸÊẾỀỂỄỆÍÌỈĨỊÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢÚÙỦŨỤƯỨỪỬỮỰÝỲỶỸỴáàảãạăắằẳẵặâấầẩẫậđéèẻẽẹêếềểễệíìỉĩịóòỏõọôốồổỗộơớờởỡợúùủũụưứừửữựýỳỷỹỵ]/, \'\')">';
                // global_search_filter += '</label>';
                var global_search_filter = '<label> Search Job ID' +
                    '<input type="search" id="global_search_filter" name="global_search_filter"' +
                    'data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" ' +
                    'data-content="<table><tr><td>Other ways to search:-</td><td></td></tr><tr><td><b>!</b><i>searchword</i></td><td>(find <i>searchword</i> from instructions of all jobs in database)</td></tr><tr><td><b>@</b><i>searchword</i></td><td>(find <i>searchword</i> from assignees of all jobs in last 3 months)</td></tr><tr><td><b>#</b><i>searchword</i></td><td>(find <i>searchword</i> from tags of all jobs in last 3 months)</td></tr></table>"' +
                    'class="form-control input-sm">';
                global_search_filter += '</label>';                
                return global_search_filter;
            },
            editor_level: function() {
                var editor_level = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_editor_level") + ' ' +
                    '<select id="editor_level_filter" multiple name="editor_level"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.editor_levels, function(idx, val) {
                    editor_level += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                editor_level += '</select></label>';
                return editor_level;
            },
            position: function() {
                var position = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_company_position") + ' ' +
                    '<select id="position_filter" multiple name="position"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.groups, function(idx, val) {
                    position += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                position += '</select></label>';
                return position;
            },
            team: function() {
                var team = '<label>' + eval("app.translations." + app.data.locale + ".filter_by_department") + ' ' +
                    '<select id="team_filter" multiple name="team" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.teams, function(idx, val) {
                    team += '<option value="' + idx + '">' + val + '</option>';
                });
                //team += '<option value="NA">na</option></select></label>';
                return team;
            },
            status: function() {
                return '<label>' + eval("app.translations." + app.data.locale + ".filter_by_status") + ' ' +
                    '<select id="status_filter" multiple name="status" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Active</option>' +
                    '<option value="2">Inactive</option>' +
                    '<option value="3">Resigned</option>' +
                    '<option value="4">Terminated</option>' +
                    '<option value="5">Pause</option>' +
                    '</select></label>';
            },
            overtimerequeststatus: function() {
                return '<label>' + eval("app.translations." + app.data.locale + ".filter_by_status") + ' ' +
                    '<select id="overtimerequeststatus_filter" multiple name="overtimerequeststatus" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Accepted</option>' +
                    '<option value="2">Pending</option>' +
                    '<option value="3">Declined</option>' +
                    '<option value="4">Deleted</option>' +
                    //'<option value="5">Option5</option>' +
                    '</select></label>';
            },
            pausetimeimported: function() {
                return '<label>' + eval("app.translations." + app.data.locale + ".filter_by_imported") + ' ' +
                    '<select id="pausetimeimported_filter" multiple name="pausetimeimported" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">From ProdTool</option>' +
                    '<option value="0">Manual</option>' +
                    '</select></label>';
            },
            enabled_disabled: function() {
                return '<label>' + eval("app.translations." + app.data.locale + ".filter_by_enabled_disabled") + ' ' +
                    '<select id="enabled_disabled_filter" multiple name="enabled_disabled" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Enabled</option>' +
                    '<option value="2">Disabled</option>' +
                    //'<option value="3">Resigned</option>' +
                    //'<option value="4">Terminated</option>' +
                    //'<option value="5">Pause</option>' +
                    '</select></label>';
            },
            currentlyinoffice: function() {
                return '<label>' + eval("app.translations." + app.data.locale + ".filter_by_currentlyinoffice") + ' ' +
                    '<select id="currentlyinoffice_filter" multiple name="currentlyinoffice" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Currently In the Office</option>' +
                    //'<option value="2">Not In the Office</option>' +
                    '</select></label>';
            },
            action: function() {
                return '<label>' + eval("app.translations." + app.data.locale + ".filter_by_action") + ' ' +
                    '<select id="action_filter" multiple name="action" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Ready</option>' +
                    '<option value="0">Not Ready</option>' +
                    '<option value="2">Resend</option>' +
                    '<option value="4">Waiting</option>' +
                    '</select></label>';
            },
            tetBonus: function() {
                return '<span><div class="switch">' +
                    '<div class="onoffswitch">' +
                    '<input type="checkbox" checked="" class="onoffswitch-checkbox" id="tetBonus">' +
                    '<label class="onoffswitch-label" for="tetBonus">' +
                    '<span class="onoffswitch-inner"></span>' +
                    '<span class="onoffswitch-switch"></span>' +
                    '</label></div></div></span>';
            },
            exportbutton: function() {
                return '<div class="dt-buttons btn-group">' +
                    '<a style="background-color: #1AB394; color: #fff; border: 1px solid #1AB394;" name="export" class="btn btn-default buttons-excel" data-action="">' +
                    '<span><span class="fa fa-file-excel-o btn-primary">' +
                    '</span>&nbsp;&nbsp;Excel Export</span></a></div>';
            },
            clearallfiltersbutton: function() {
                return '<div class="dt-buttons btn-group" style="margin-right: 5px">' +
                    '<a name="clearallfilters" class="btn btn-default buttons-excel" data-action="">' +
                    '<span>Clear All Filters</span></a></div>';
            }
        },
        build2: {
            editor_level2: function() {
                var editor_level = '<label>Filter by Editor Level ' +
                    '<select id="editor_level_filter2" multiple name="editor_level2"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.editor_levels, function(idx, val) {
                    editor_level += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                editor_level += '</select></label>';
                return editor_level;
            },
            position2: function() {
                var position = '<label>Filter by Company Position ' +
                    '<select id="position_filter2" multiple name="position2"' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.groups, function(idx, val) {
                    position += '<option value="' + val.id + '">' + val.name + '</option>';
                });
                position += '</select></label>';
                return position;
            },
            team2: function() {
                var team = '<label>Filter by Department ' +
                    '<select id="team_filter2" multiple name="team2" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">';
                //'<option value="">All</option>';
                $.each(app.data.teams, function(idx, val) {
                    team += '<option value="' + idx + '">' + val + '</option>';
                });
                team += '<option value="NA">na</option></select></label>';
                return team;
            },
            status2: function() {
                return '<label>Filter by Status ' +
                    '<select id="status_filter2" multiple name="status2" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Active</option>' +
                    '<option value="2">Inactive</option>' +
                    '<option value="3">Resigned</option>' +
                    '<option value="4">Terminated</option>' +
                    '<option value="5">Pause</option>' +
                    '</select></label>';
            },
            action2: function() {
                return '<label>Filter by Action ' +
                    '<select id="action_filter2" multiple name="action2" ' +
                    //'aria-controls="user" ' +
                    'class="form-control input-sm">' +
                    //'<option value="">All</option>' +
                    '<option value="1">Ready</option>' +
                    '<option value="0">Not Ready</option>' +
                    '<option value="2">Resend</option>' +
                    '<option value="4">Waiting</option>' +
                    '</select></label>';
            },
            tetBonus2: function() {
                return '<span><div class="switch">' +
                    '<div class="onoffswitch">' +
                    '<input type="checkbox" checked="" class="onoffswitch-checkbox" id="tetBonus">' +
                    '<label class="onoffswitch-label" for="tetBonus">' +
                    '<span class="onoffswitch-inner"></span>' +
                    '<span class="onoffswitch-switch"></span>' +
                    '</label></div></div></span>';
            },
            exportbutton2: function() {
                return '<div class="dt-buttons btn-group">' +
                    '<a style="background-color: #1AB394; color: #fff; border: 1px solid #1AB394;" name="export2" class="btn btn-default buttons-excel" data-action="">' +
                    '<span><span class="fa fa-file-excel-o btn-primary">' +
                    '</span>&nbsp;&nbsp;Excel Export</span></a></div>';
            },
            clearallfiltersbutton2: function() {
                return '<div class="dt-buttons btn-group" style="margin-right: 5px;">' +
                    '<a name="clearallfilters2" class="btn btn-default buttons-excel" data-action="">' +
                    '<span>Clear All Filters</span></a></div>';
            }
        },
        fixedheaderviewporthandler: function() {
            var numbertoremember = 0;
            var fixed_header_shift = 35;
            var horizontal = null;
            var vertical = null;
            var whatisthis = null;
            var whatisthis2 = null;
            var numbertouse = null;

            document.addEventListener('scroll', function(event) {
                //console.log('scrolling', event.target.className);
                //console.log($(event.target).parent().attr('id'));
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'employeesInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'employeefamilymembersInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'employeecontractsInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'employeegeneraldocumentsInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'customRCMessageSchedulerInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'workingshiftsInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'defaultshiftInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });

                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'twoweektimesheetshiftplannerInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'attendancestatusInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'companydepartmentInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'companypositionInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'overtimesrequestInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }


                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'AccountingEMPLOYERPAYROLLInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'attendancetimesInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'timesheetsinfoInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'timesheets2wsinfoInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'timesheetmonthlyInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'twoweektimesheetshiftplannerInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'workingtimesInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'pausetimeInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'downloadlistInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'manualdownloadlistInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }


                /** special not the same as above */
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'bankreportInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        fixed_header_shift = 20;
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }
                if (event.target.className == 'table-responsive' && $(event.target).parent().attr('id') == 'payrollInfo') {
                    $(".table-responsive").on("scroll", function(e) {
                        horizontal = e.currentTarget.scrollLeft;
                        vertical = e.currentTarget.scrollTop;
                        numbertoremember = horizontal * -1;
                        whatisthis = $(".table-responsive").scrollLeft();
                        whatisthis2 = $(".table-responsive").width();
                        numbertouse = whatisthis2 + whatisthis - 1;
                        //console.log(numbertouse.toFixed(2));
                        $('.controlsfortable').css({
                            'padding-left': horizontal,
                            'width': numbertouse.toFixed(2)
                        });
                        fixed_header_shift = 20;
                        $('.fixedHeader-floating').css({
                            'left': numbertoremember + fixed_header_shift
                        });
                        var position_filter_location = $("#position_filter .ms-options-wrap").position();
                        var team_filter_location = $("#team_filter .ms-options-wrap").position();
                        var status_filter_location = $("#status_filter .ms-options-wrap").position();
                        var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();

                        if (position_filter_location != null) {
                            $('#position_filter .ms-options').css({
                                'left': position_filter_location.left,
                                'top': position_filter_location.top + 29
                            });
                        }
                        if (team_filter_location != null) {
                            $("#team_filter .ms-options").css({
                                'left': team_filter_location.left,
                                'top': team_filter_location.top + 29
                            });
                        }
                        if (status_filter_location != null) {
                            $("#status_filter .ms-options").css({
                                'left': status_filter_location.left,
                                'top': status_filter_location.top + 29
                            });
                        }
                        if (editor_level_filter_location != null) {
                            $("#editor_level_filter .ms-options").css({
                                'left': editor_level_filter_location.left,
                                'top': editor_level_filter_location.top + 29
                            });
                        }
                    });
                }

                // if($('.fixedHeader-floating').children("thead").is(":visible")){
                //     $('.gray-bg').css('background-color', '#f3f3f4');
                // }else{
                //     $('.gray-bg').css('background-color', '');
                // }

            }, true /*Capture event*/ );

            $(window).on('scroll', function(e) {
                /** */
                /** */
            });

            $(window).resize(function() {
                $('.controlsfortable').css({ 'width': '' });
                var position_filter_location = $("#position_filter .ms-options-wrap").position();
                var team_filter_location = $("#team_filter .ms-options-wrap").position();
                var status_filter_location = $("#status_filter .ms-options-wrap").position();
                var editor_level_filter_location = $("#editor_level_filter .ms-options-wrap").position();
                if (position_filter_location != null) {
                    $('#position_filter .ms-options').css({
                        'left': position_filter_location.left,
                        'top': position_filter_location.top + 29
                    });
                }
                if (team_filter_location != null) {
                    $("#team_filter .ms-options").css({
                        'left': team_filter_location.left,
                        'top': team_filter_location.top + 29
                    });
                }
                if (status_filter_location != null) {
                    $("#status_filter .ms-options").css({
                        'left': status_filter_location.left,
                        'top': status_filter_location.top + 29
                    });
                }
                if (editor_level_filter_location != null) {
                    $("#editor_level_filter .ms-options").css({
                        'left': editor_level_filter_location.left,
                        'top': editor_level_filter_location.top + 29
                    });
                }
            });
        },
        period: function() {
            var periodControl = '<div class="row left previous-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-left previous-month" ' +
                'aria-hidden="true" title="Previous Month" data-url="/prev_fullpage"></span></div>' +
                '<div class="row right next-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-right next-month" ' +
                'aria-hidden="true" title="Next Month" data-url="/next_fullpage"></span></div>';
            $('#main').append(periodControl);

            if (!app.data.period) {
                app.data.period = [];
                app.data.period.canNext = false;
                app.data.period.canPrev = false;

                var changedDate = new Date();
                var year = changedDate.getFullYear();
                var month = changedDate.getMonth() + 1; //it was selecting the previous month so add one
                var date = changedDate.getDate();
                if (month == '12') {
                    year += 1;
                }
                var realDate = new Date(year, month, date);
                var toDate = realDate.toISOString().substr(0, 10);
                app.data.period.gotoUrl = '/goto_ajax/' + toDate;
            }
            if (!app.data.period.canNext) {
                $('.next-month').toggleClass('disabled');
            }
            if (!app.data.period.canPrev) {
                $('.previous-month').toggleClass('disabled');
            }
            $('.next-month, .previous-month').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                window.location = $(this).data('url');
            });
            $('.glyphicon-chevron-right, .glyphicon-chevron-left, .bfh-flag-VN, .bfh-flag-GB').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                NProgress.start();
                app.util.fullscreenloading_start();
            });

            var $period = $('.period');
            var $datepicker = $period.datepicker({
                format: "yyyy-mm-01",
                minViewMode: 1,
                endDate: "today",
                toggleActive: true,
                autoclose: true,
                startDate: app.data.period.start
            });
            // $period.datepicker('update', new Date(app.data.period.date));
            $datepicker.on('changeMonth', function(e) {
                NProgress.start();
                app.util.fullscreenloading_start();
                var changedDate = new Date(e.date);
                var year = changedDate.getFullYear();
                var month = changedDate.getMonth() + 1; //it was selecting the previous month so add one
                var date = changedDate.getDate();
                if (month == '12') {
                    year += 1;
                }
                var realDate = new Date(year, month, date);
                var toDate = realDate.toISOString().substr(0, 10);
                //console.log(app.data.period);
                //console.log(toDate);
                window.location = app.data.period.gotoUrl + '/' + toDate;
            });


            var $profileperiod = $('.profileperiod');
            var $profiledatepicker = $profileperiod.datepicker({
                format: "yyyy-mm-01",
                minViewMode: 1,
                endDate: "today",
                toggleActive: true,
                autoclose: true,
                startDate: app.data.period.start
            });

            //you commented this out but did not explain why.. looks to be related to the employeeprofile page that is up in place.
            // This page is non period dependent
            // $profileperiod.datepicker('update', new Date(app.data.period.date));
            // $profiledatepicker.on('changeMonth', function(e) {
            //     NProgress.start();
            //     var changedDate = new Date(e.date);
            //     var year = changedDate.getFullYear();
            //     var month = changedDate.getMonth() + 1; //it was selecting the previous month so add one
            //     var date = changedDate.getDate();
            //     if (month == '12') {
            //         year += 1;
            //     }
            //     var realDate = new Date(year, month, date);
            //     var toDate = realDate.toISOString().substr(0, 10);
            //     console.log(app.data.period);
            //     //when the date is not important?
            //     //window.location = '/employeeprofile/' + toDate;
            // });

            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    //NProgress.start();
                }
            });

        },
        period_fullpage: function() {
            //console.log(app.data.timesheet_period);
            var periodControl = '<div class="row left previous-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-left previous-month" ' +
                'aria-hidden="true" title="Previous Month" data-url="/prev_fullpage"></span></div>' +
                '<div class="row right next-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-right next-month" ' +
                'aria-hidden="true" title="Next Month" data-url="/next_fullpage"></span></div>';
            $('#main').append(periodControl);

            if (!app.data.timesheet_period) {
                app.data.timesheet_period = [];
                app.data.timesheet_period.canNext = false;
                app.data.timesheet_period.canPrev = false;
            }
            if (!app.data.timesheet_period.canNext) {
                $('.next-month').toggleClass('disabled');
                //
            }
            if (!app.data.timesheet_period.canPrev) {
                $('.previous-month').toggleClass('disabled');
                //
            }

            $('.next-month, .previous-month').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                window.location = $(this).data('url');
            });

            $('.glyphicon-chevron-right, .glyphicon-chevron-left, .bfh-flag-VN, .bfh-flag-GB').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                NProgress.start();
                app.util.fullscreenloading_start();
            });

            var prefered_dateFormat = null;
            var patt = null;
            var prefered_dateFormat_placeholder = null;
            var number_format_per_locale = null;
            var delimiter_for_splitting_variable = null;
            var numberformat_locale = null;
            if (app.data.locale === 'vi') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'en') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'de') {
                prefered_dateFormat = "dd.mm.yy";
                prefered_dateFormat_placeholder = "dd.mm.yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '.';
            }

            if (app.data.currency == 'VND') {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            } else if (app.data.currency == 'USD') {
                number_format_per_locale = '$ #,##0.00';
                numberformat_locale = 'en';
            } else if (app.data.currency == 'EUR') {
                number_format_per_locale = '€ #,##0.00';
                numberformat_locale = 'de';
            } else {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            }

            var counting_ctd = 1;
            var $period_datepicker = $('.period_datepicker');
            $period_datepicker.datepicker({
                dateFormat: prefered_dateFormat,
                showMonthAfterYear: true,
                numberOfMonths: 2,
                changeMonth: true,
                changeYear: true,
                yearRange: "-10:+1",
                showOtherMonths: false,
                selectOtherMonths: true,
                toggleActive: true,
                todayHighlight: false,
                minDate: "-10y",
                maxDate: "+1y",
                autoclose: true,
                defaultDate: null,
                onSelect: function() {
                    /**you need to format the date before sending */
                    /** from locale to expected YYYY-MM-DD */
                    var theselecteddate = $('.period_datepicker').val();
                    //console.log(theselecteddate);
                    var from = theselecteddate.split(delimiter_for_splitting_variable);
                    //console.log(from);
                    if (app.data.locale === 'vi') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    } else if (app.data.locale === 'en') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    } else if (app.data.locale === 'de') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    }
                    //console.log(datetogoto);
                    /** should i convert to timestamp? */
                    window.location = app.data.timesheet_period.gotoUrl + '/' + datetogoto;
                },
                onClose: function() {
                    var addressinput = $(this).val();
                    /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                    var res = patt.test(addressinput);

                    // if (res == true) {
                    //     $('#contract_date').closest('td').removeClass('has-error');
                    //     $('#contract_date').nextAll('.help-block').css('display', 'none');

                    //     $(this).blur();
                    //     counting_ctd = 1;
                    // } else {
                    //     $("#contract_date").closest("td").addClass("has-error");
                    //     if (counting_ctd == 1) {
                    //         $("#contract_date").after('<span class="help-block"><strong>' + eval("app.translations."+app.data.locale+".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations."+app.data.locale+".or_choose_from_the_calendar") + '</strong></span>');
                    //     } else {
                    //         // it exists
                    //     }
                    //     counting_ctd++;
                    //     $(this).blur();
                    // }
                },
                beforeShow: function(input, obj) {
                    // $period_datepicker.after($period_datepicker.datepicker('widget'));
                    var the_input_top = $('.period_datepicker').offset().top;
                    var the_input_left = $('.period_datepicker').offset().left;
                    setTimeout(function() {
                        $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                        $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                    }, 0);
                },
                beforeShowDay: function(date) {
                    var formated = formatDate(date);
                    var your_dates = Object.keys(app.data.timesheet_period.range_array_days_formated).map(function(key) {
                        return app.data.timesheet_period.range_array_days_formated[key];
                    });
                    // check if date is in your array of dates
                    function formatDate(date) {
                        var d = new Date(date),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        return [year, month, day].join('-');
                    }

                    var todaydate = app.data.timesheet_period.today;

                    function formattodayDate(todaydate) {
                        var d = new Date(todaydate),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        return [year, month, day].join('-');
                    }


                    var highlight_today = formattodayDate(todaydate);
                    if (formated == highlight_today) {
                        return [true, "ui-state-active shift_planner_datepicker_today", ''];
                    }

                    //console.log('formated='+formated);
                    if ($.inArray(formated, your_dates) != -1) {
                        // if it is return the following.
                        return [true, 'ui-state-active', ''];
                    } else {
                        // default
                        return [true, '', ''];
                    }
                }
            });

            $("#attendance_status_change_period").mouseenter(function() {
                $(this).addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
            }).mouseleave(function() {
                var constantlychecking_if_timer = setInterval(function() {
                    var terminatedatepicker_visiblecheck = $('.period_datepicker').datepicker("widget").is(":visible");
                    if (terminatedatepicker_visiblecheck == true) {
                        $("#attendance_status_change_period").addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
                    } else {
                        $("#attendance_status_change_period").addClass('btn-white').addClass('grey_placeholder').removeClass('btn-primary').removeClass('white_placeholder');
                        clearInterval(constantlychecking_if_timer);
                    }
                }, 100);
            });

            var resizeTimer;
            var scrollTimer;
            $(window).scroll(function() {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $(window).resize(function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    NProgress.start();
                }
            });
        },
        period_ajax_cache: function() {
            var periodControl = '<div class="row left previous-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-left previous-month" ' +
                'aria-hidden="true" title="Previous Month" data-url="/prev_ajax"></span></div>' +
                '<div class="row right next-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-right next-month" ' +
                'aria-hidden="true" title="Next Month" data-url="/next_ajax"></span></div>';
            $('#main').append(periodControl);

            if (!app.data.timesheet_period) {
                app.data.timesheet_period = [];
                app.data.timesheet_period.canNext = false;
                app.data.timesheet_period.canPrev = false;
            }
            if (!app.data.timesheet_period.canNext) {
                $('.next-month').toggleClass('disabled');
                //
            }
            if (!app.data.timesheet_period.canPrev) {
                $('.previous-month').toggleClass('disabled');
                //
            }

            $('.next-month, .previous-month').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }

                var d = new Date(app.data.timesheet_period.date);
                var check_backwards_driection = $(this).attr('class').indexOf("previous-month");
                if (check_backwards_driection >= 0) { d.setMonth(d.getMonth() - 1); }
                var check_forwards_driection = $(this).attr('class').indexOf("next-month");
                if (check_forwards_driection >= 0) { d.setMonth(d.getMonth() + 1); }
                var year_full = d.getFullYear();
                var month_short = d.toLocaleString('default', { month: 'short' });

                /** new method to change the period without reloading the page just the table. */
                /** is it possible to ajax to the url instead modify the cache then trigger the ajax reload. which takes the new cache value */
                var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
                app.ajax.jsonGET($(this).data('url'), data, null, function() {
                    NProgress.start();
                    app.util.fullscreenloading_start();
                    //$('#twoweektimesheetshiftplannerInfo').css('display', 'none');
                    //console.log(app.ajax.result);
                    success_ajax_then_refresh = app.ajax.result.success;
                    if (app.ajax.result.success == true) {
                        /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
                        /** the headers are also in need of */
                        window.timesheetmonthly.destroy();

                        app_attendance.ajaxexample6rlm.profile.get_timesheetmonthlyInfo_tab();
                        var onlycallonce = 1;
                        $(document).ajaxStop(function() {
                            if (onlycallonce == 1) {
                                //console.log("All AJAX requests completed");
                                //app_attendance.ajaxexample6rlm.profile.table();
                                app_attendance.ajaxexample6rlm.profile.add_edit_timesheet_monthly_info_table();
                                onlycallonce = 2;
                                NProgress.done();
                                app.util.fullscreenloading_end();
                                $('#timesheetmonthlyInfo').css('display', 'block');
                                $('#ibox_title_heading').html('Employee Timesheet Info in Month of ' + month_short + ', ' + year_full);
                            }
                        });

                    } else {
                        /** */
                        /** */
                        //console.log('NOT working');
                        NProgress.done();
                        app.util.fullscreenloading_end();
                    }
                });
            });

            $('.glyphicon-chevron-right, .glyphicon-chevron-left, .bfh-flag-VN, .bfh-flag-GB').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                NProgress.start();
                app.util.fullscreenloading_start();
            });

            var prefered_dateFormat = null;
            var prefered_month_year_dateFormat = null;
            var patt = null;
            var mm_yyyy_patt = null;
            var prefered_dateFormat_placeholder = null;
            var prefered_month_year_dateFormat_placeholder = null;
            var number_format_per_locale = null;
            var delimiter_for_splitting_variable = null;
            var numberformat_locale = null;
            if (app.data.locale === 'vi') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_month_year_dateFormat = "mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                prefered_month_year_dateFormat_placeholder = "mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                mm_yyyy_patt = new RegExp("^([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'en') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_month_year_dateFormat = "mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                prefered_month_year_dateFormat_placeholder = "mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                mm_yyyy_patt = new RegExp("^([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'de') {
                prefered_dateFormat = "dd.mm.yy";
                prefered_month_year_dateFormat = "mm.dd";
                prefered_dateFormat_placeholder = "dd.mm.yyyy";
                prefered_month_year_dateFormat_placeholder = "mm.yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                mm_yyyy_patt = new RegExp("^([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '.';
            }

            if (app.data.currency == 'VND') {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            } else if (app.data.currency == 'USD') {
                number_format_per_locale = '$ #,##0.00';
                numberformat_locale = 'en';
            } else if (app.data.currency == 'EUR') {
                number_format_per_locale = '€ #,##0.00';
                numberformat_locale = 'de';
            } else {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            }


            var default_datepicker_date_value = app.data.default_datepicker_date;
            var default_datepicker_date = default_datepicker_date_value.split('-');
            /** if i can somehow use the cache period to set the default date on open */
            var default_minD_YYYY = parseInt(default_datepicker_date[0]);
            var default_minD_MM = parseInt(default_datepicker_date[1]) - 1;
            var default_minD_DD = 1;
            var defaultDateVARIABLE = new Date(default_minD_YYYY, default_minD_MM, default_minD_DD)



            var counting_ctd = 1;
            var $period_datepicker = $('.period_datepicker');
            $period_datepicker.bind('keydown', function(e) {
                if (e.which == 13) {
                    e.stopImmediatePropagation();
                }
                if (e.which == 27) {
                    $(this).blur();
                }
            }).datepicker({
                dateFormat: prefered_month_year_dateFormat,
                showMonthAfterYear: true,
                showButtonPanel: true,
                numberOfMonths: 1,
                showCurrentAtPos: 0,
                changeMonth: true,
                changeYear: true,
                yearRange: "-2:+2",
                showOtherMonths: false,
                selectOtherMonths: false,
                toggleActive: true,
                todayHighlight: false,
                minDate: "-10y",
                maxDate: "+1y",
                autoclose: true,
                defaultDate: defaultDateVARIABLE,

                onClose: function(dateText, inst) {

                    function isDonePressed() {
                        return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                    }

                    if (isDonePressed()) {
                        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                        $period_datepicker.datepicker('setDate', new Date(year, month, 1)).trigger('change');
                        $period_datepicker.focusout(); //Added to remove focus from datepicker input box on selecting date

                        var theselecteddate = $('.period_datepicker').val();
                        //console.log(theselecteddate);
                        var from = theselecteddate.split(delimiter_for_splitting_variable);
                        //console.log(from);
                        var datetogoto = null;
                        var default_dateYYY = null;
                        var default_dateMM = null;
                        var default_dateDD = 1;
                        if (app.data.locale === 'vi') {
                            var datetogoto = parseInt(from[1]) + '-' + parseInt(from[0]) + '-' + parseInt(1);
                            default_dateYYY = parseInt(from[1]);
                            default_dateMM = parseInt(from[0]);
                        } else if (app.data.locale === 'en') {
                            var datetogoto = parseInt(from[1]) + '-' + parseInt(from[0]) + '-' + parseInt(1);
                            default_dateYYY = parseInt(from[1]);
                            default_dateMM = parseInt(from[0]);
                        } else if (app.data.locale === 'de') {
                            var datetogoto = parseInt(from[1]) + '-' + parseInt(from[0]) + '-' + parseInt(1);
                            default_dateYYY = parseInt(from[1]);
                            default_dateMM = parseInt(from[0]);
                        }

                        //console.log(datetogoto);

                        var d = new Date(datetogoto);
                        var year_full = d.getFullYear();
                        var month_short = d.toLocaleString('default', { month: 'short' });

                        var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
                        app.ajax.jsonGET(app.data.timesheet_period.gotoUrl + '/' + datetogoto, data, null, function() {
                            NProgress.start();
                            app.util.fullscreenloading_start();
                            $('#twoweektimesheetshiftplannerInfo').css('display', 'none');
                            //console.log(app.ajax.result);
                            success_ajax_then_refresh = app.ajax.result.success;
                            if (app.ajax.result.success == true) {
                                /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
                                /** the headers are also in need of */
                                window.timesheetmonthly.destroy();

                                app_attendance.ajaxexample6rlm.profile.get_timesheetmonthlyInfo_tab();
                                var onlycallonce = 1;
                                $(document).ajaxStop(function() {
                                    if (onlycallonce == 1) {
                                        //console.log("All AJAX requests completed");
                                        //app_attendance.ajaxexample6rlm.profile.table();
                                        app_attendance.ajaxexample6rlm.profile.add_edit_timesheet_monthly_info_table();
                                        onlycallonce = 2;
                                        NProgress.done();
                                        app.util.fullscreenloading_end();
                                        $('#timesheetmonthlyInfo').css('display', 'block');
                                        $('#ibox_title_heading').html('Employee Timesheet Info in Month of ' + month_short + ', ' + year_full);
                                        defaultDateVARIABLE = new Date(default_dateYYY, default_dateMM, default_dateDD);
                                        $period_datepicker.datepicker("option", "defaultDate", defaultDateVARIABLE);
                                    }
                                });


                            } else {
                                /** */
                                /** */
                                //console.log('NOT working');
                                NProgress.done();
                                app.util.fullscreenloading_end();
                            }
                        });

                    }
                    setTimeout(function() {
                        inst.dpDiv.removeClass('calendar-off');
                    }, 250);

                    // var addressinput = $(this).val();
                    // /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                    // var res = mm_yyyy_patt.test(addressinput);

                    // if (res == true) {
                    //     $('#payment_period_description').closest('td').removeClass('has-error');
                    //     $('#payment_period_description').nextAll('.help-block').css('display', 'none');

                    //     $(this).blur();
                    //     counting_my_ssd = 1;
                    // } else {
                    //     $("#payment_period_description").closest("td").addClass("has-error");
                    //     if (counting_my_ssd == 1) {
                    //         $("#payment_period_description").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_month_year_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                    //     } else {
                    //         // it exists
                    //     }
                    //     counting_my_ssd++;
                    //     $(this).blur();
                    // }
                },

                onChangeMonthYear: function() {
                    /**console.log('changed using prev or next or other');*/
                    var the_input_top = $('.period_datepicker').offset().top;
                    var the_input_left = $('.period_datepicker').offset().left;
                    setTimeout(function() {
                        $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 110).css('z-index', 102);
                        $('.ui-datepicker-close').mouseenter(function() {
                            $(this).addClass('ui-state-hover');
                        }).mouseleave(function() {
                            $(this).removeClass('ui-state-hover');
                        });
                    }, 0);
                },
                beforeShow: function(input, inst) {
                    var the_input_top = $('.period_datepicker').offset().top;
                    var the_input_left = $('.period_datepicker').offset().left;

                    setTimeout(function() {
                        $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 110).css('z-index', 102);
                        $('.ui-datepicker-close').mouseenter(function() {
                            $(this).addClass('ui-state-hover');
                        }).mouseleave(function() {
                            $(this).removeClass('ui-state-hover');
                        });
                    }, 0);

                    inst.dpDiv.addClass('calendar-off');
                }
            });



            // var counting_ctd = 1;
            // var $period_datepicker = $('.period_datepicker');
            // $period_datepicker.datepicker({
            //     dateFormat: prefered_dateFormat,
            //     showMonthAfterYear: true,
            //     numberOfMonths: 2,
            //     showCurrentAtPos: 1,
            //     changeMonth: true,
            //     changeYear: true,
            //     yearRange: "-10:+1",
            //     showOtherMonths: false,
            //     selectOtherMonths: true,
            //     toggleActive: true,
            //     todayHighlight: false,
            //     minDate: "-10y",
            //     maxDate: "+1y",
            //     autoclose: true,
            //     defaultDate: defaultDateVARIABLE,
            //     onSelect: function() {
            //         /**you need to format the date before sending */
            //         /** from locale to expected YYYY-MM-DD */
            //         var theselecteddate = $('.period_datepicker').val();
            //         //console.log(theselecteddate);
            //         var from = theselecteddate.split(delimiter_for_splitting_variable);
            //         //console.log(from);
            //         var datetogoto = null;
            //         var default_dateYYY = null;
            //         var default_dateMM = null;
            //         var default_dateDD = 1;
            //         if (app.data.locale === 'vi') {
            //             var datetogoto =  parseInt(from[1]) + '-' + parseInt(from[0]) + '-' + parseInt(1);
            //             default_dateYYY = parseInt(from[1]);
            //             default_dateMM = parseInt(from[0]);
            //         } else if (app.data.locale === 'en') {
            //             var datetogoto = parseInt(from[1]) + '-' + parseInt(from[0]) + '-' + parseInt(1);
            //             default_dateYYY = parseInt(from[1]);
            //             default_dateMM = parseInt(from[0]);
            //         } else if (app.data.locale === 'de') {
            //             var datetogoto = parseInt(from[1]) + '-' + parseInt(from[0]) + '-' + parseInt(1);
            //             default_dateYYY = parseInt(from[1]);
            //             default_dateMM = parseInt(from[0]);
            //         }

            //         //console.log(datetogoto);

            //         var d = new Date(datetogoto);
            //         var year_full = d.getFullYear();
            //         var month_short = d.toLocaleString('default', { month: 'short' });

            //         var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
            //         app.ajax.jsonGET(app.data.timesheet_period.gotoUrl + '/' + datetogoto, data, null, function() {
            //             NProgress.start();
            //             app.util.fullscreenloading_start();
            //             $('#twoweektimesheetshiftplannerInfo').css('display', 'none');
            //             //console.log(app.ajax.result);
            //             success_ajax_then_refresh = app.ajax.result.success;
            //             if (app.ajax.result.success == true) {
            //                 /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
            //                 /** the headers are also in need of */
            //                 window.timesheetmonthly.destroy();

            //                 app_attendance.ajaxexample6rlm.profile.get_timesheetmonthlyInfo_tab();
            //                 var onlycallonce = 1;
            //                 $(document).ajaxStop(function() {
            //                     if (onlycallonce == 1) {
            //                         //console.log("All AJAX requests completed");
            //                         //app_attendance.ajaxexample6rlm.profile.table();
            //                         app_attendance.ajaxexample6rlm.profile.add_edit_timesheet_monthly_info_table();
            //                         onlycallonce = 2;
            //                         NProgress.done();
            //                         app.util.fullscreenloading_end();
            //                         $('#timesheetmonthlyInfo').css('display', 'block');
            //                         $('#ibox_title_heading').html('Employee Timesheet Info in Month of ' + month_short + ', ' + year_full);
            //                         defaultDateVARIABLE = new Date(default_dateYYY, default_dateMM, default_dateDD);
            //                         $period_datepicker.datepicker("option", "defaultDate", defaultDateVARIABLE);
            //                     }
            //                 });


            //             } else {
            //                 /** */
            //                 /** */
            //                 //console.log('NOT working');
            //                 NProgress.done();
            //                 app.util.fullscreenloading_end();
            //             }
            //         });

            //     },
            //     onClose: function() {
            //         var addressinput = $(this).val();
            //         /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
            //         var res = patt.test(addressinput);

            //         // if (res == true) {
            //         //     $('#contract_date').closest('td').removeClass('has-error');
            //         //     $('#contract_date').nextAll('.help-block').css('display', 'none');

            //         //     $(this).blur();
            //         //     counting_ctd = 1;
            //         // } else {
            //         //     $("#contract_date").closest("td").addClass("has-error");
            //         //     if (counting_ctd == 1) {
            //         //         $("#contract_date").after('<span class="help-block"><strong>' + eval("app.translations."+app.data.locale+".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations."+app.data.locale+".or_choose_from_the_calendar") + '</strong></span>');
            //         //     } else {
            //         //         // it exists
            //         //     }
            //         //     counting_ctd++;
            //         //     $(this).blur();
            //         // }
            //     },
            //     beforeShow: function(input, obj) {
            //         // $period_datepicker.after($period_datepicker.datepicker('widget'));
            //         var the_input_top = $('.period_datepicker').offset().top;
            //         var the_input_left = $('.period_datepicker').offset().left;
            //         setTimeout(function() {
            //             $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
            //             $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
            //         }, 0);
            //     },
            //     onChangeMonthYear: function() {
            //         var the_input_top = $('.period_datepicker').offset().top;
            //         var the_input_left = $('.period_datepicker').offset().left;
            //         setTimeout(function() {
            //             $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
            //             $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
            //         }, 0);
            //     },
            //     beforeShowDay: function(date) {
            //         var formated = formatDate(date);
            //         var your_dates = Object.keys(app.data.timesheet_period.range_array_days_formated).map(function(key) {
            //             return app.data.timesheet_period.range_array_days_formated[key];
            //         });
            //         // check if date is in your array of dates
            //         function formatDate(date) {
            //             var d = new Date(date),
            //                 month = '' + (d.getMonth() + 1),
            //                 day = '' + d.getDate(),
            //                 year = d.getFullYear();

            //             if (month.length < 2) month = '0' + month;
            //             if (day.length < 2) day = '0' + day;

            //             return [year, month, day].join('-');
            //         }

            //         var todaydate = app.data.timesheet_period.today;

            //         function formattodayDate(todaydate) {
            //             var d = new Date(todaydate),
            //                 month = '' + (d.getMonth() + 1),
            //                 day = '' + d.getDate(),
            //                 year = d.getFullYear();

            //             if (month.length < 2) month = '0' + month;
            //             if (day.length < 2) day = '0' + day;

            //             return [year, month, day].join('-');
            //         }


            //         var highlight_today = formattodayDate(todaydate);
            //         if (formated == highlight_today) {
            //             return [true, "ui-state-active shift_planner_datepicker_today", ''];
            //         }

            //         //console.log('formated='+formated);
            //         if ($.inArray(formated, your_dates) != -1) {
            //             // if it is return the following.
            //             return [true, 'ui-state-active', ''];
            //         } else {
            //             // default
            //             return [true, '', ''];
            //         }
            //     }
            // });

            $("#attendance_status_change_period").mouseenter(function() {
                $(this).addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
            }).mouseleave(function() {
                var constantlychecking_if_timer = setInterval(function() {
                    var terminatedatepicker_visiblecheck = $('.period_datepicker').datepicker("widget").is(":visible");
                    if (terminatedatepicker_visiblecheck == true) {
                        $("#attendance_status_change_period").addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
                    } else {
                        $("#attendance_status_change_period").addClass('btn-white').addClass('grey_placeholder').removeClass('btn-primary').removeClass('white_placeholder');
                        clearInterval(constantlychecking_if_timer);
                    }
                }, 100);
            });

            var resizeTimer;
            var scrollTimer;
            $(window).scroll(function() {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $(window).resize(function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    NProgress.start();
                }
            });
        },
        timesheet_period_fullpage: function() {
            console.log('timesheet_period_fullpage');
            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    NProgress.start();
                }
            });

            var periodControl = '<div class="row left previous-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-left previous-two-weeks" ' +
                'aria-hidden="true" title="Previous Two Weeks" data-url="/prev_two_weeks_fullpage"></span></div>' +
                '<div class="row right next-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-right next-two-weeks" ' +
                'aria-hidden="true" title="Next Two Weeks" data-url="/next_two_weeks_fullpage"></span></div>';

            $('#main').append(periodControl);

            if (!app.data.timesheet_period) {
                app.data.timesheet_period = [];
                app.data.timesheet_period.canNextTwoWeeks = false;
                app.data.timesheet_period.canPrevTwoWeeks = false;
            }
            if (!app.data.timesheet_period.canNextTwoWeeks) {
                $('.next-two-weeks').toggleClass('disabled');
                //
            }
            if (!app.data.timesheet_period.canPrevTwoWeeks) {
                $('.previous-two-weeks').toggleClass('disabled');
                //
            }

            $('.next-two-weeks, .previous-two-weeks').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                window.location = $(this).data('url');
            });

            $('.glyphicon-chevron-right, .glyphicon-chevron-left, .bfh-flag-VN, .bfh-flag-GB').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                NProgress.start();
                app.util.fullscreenloading_start();
            });

            var prefered_dateFormat = null;
            var patt = null;
            var prefered_dateFormat_placeholder = null;
            var number_format_per_locale = null;
            var delimiter_for_splitting_variable = null;
            var numberformat_locale = null;
            if (app.data.locale === 'vi') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'en') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'de') {
                prefered_dateFormat = "dd.mm.yy";
                prefered_dateFormat_placeholder = "dd.mm.yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '.';
            }

            if (app.data.currency == 'VND') {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            } else if (app.data.currency == 'USD') {
                number_format_per_locale = '$ #,##0.00';
                numberformat_locale = 'en';
            } else if (app.data.currency == 'EUR') {
                number_format_per_locale = '€ #,##0.00';
                numberformat_locale = 'de';
            } else {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            }

            var counting_ctd = 1;
            var $period_datepicker = $('.period_datepicker');
            $period_datepicker.datepicker({
                dateFormat: prefered_dateFormat,
                showMonthAfterYear: true,
                numberOfMonths: 2,
                changeMonth: true,
                changeYear: true,
                yearRange: "-10:+1",
                showOtherMonths: false,
                selectOtherMonths: true,
                toggleActive: true,
                todayHighlight: false,
                minDate: "-10y",
                maxDate: "+1y",
                autoclose: true,
                defaultDate: null,
                onSelect: function() {
                    /**you need to format the date before sending */
                    /** from locale to expected YYYY-MM-DD */
                    var theselecteddate = $('.period_datepicker').val();
                    //console.log(theselecteddate);
                    var from = theselecteddate.split(delimiter_for_splitting_variable);
                    //console.log(from);
                    if (app.data.locale === 'vi') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    } else if (app.data.locale === 'en') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    } else if (app.data.locale === 'de') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    }
                    //console.log(datetogoto);
                    /** should i convert to timestamp? */
                    window.location = app.data.timesheet_period.gotoTwoWeeksUrl + '/' + datetogoto;
                },
                onClose: function() {
                    var addressinput = $(this).val();
                    /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                    var res = patt.test(addressinput);

                    // if (res == true) {
                    //     $('#contract_date').closest('td').removeClass('has-error');
                    //     $('#contract_date').nextAll('.help-block').css('display', 'none');

                    //     $(this).blur();
                    //     counting_ctd = 1;
                    // } else {
                    //     $("#contract_date").closest("td").addClass("has-error");
                    //     if (counting_ctd == 1) {
                    //         $("#contract_date").after('<span class="help-block"><strong>' + eval("app.translations."+app.data.locale+".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations."+app.data.locale+".or_choose_from_the_calendar") + '</strong></span>');
                    //     } else {
                    //         // it exists
                    //     }
                    //     counting_ctd++;
                    //     $(this).blur();
                    // }
                },
                beforeShow: function(input, obj) {
                    // $period_datepicker.after($period_datepicker.datepicker('widget'));
                    var the_input_top = $('.period_datepicker').offset().top;
                    var the_input_left = $('.period_datepicker').offset().left;
                    setTimeout(function() {
                        $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                        $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                    }, 0);
                },
                beforeShowDay: function(date) {
                    var formated = formatDate(date);
                    var your_dates = Object.keys(app.data.timesheet_period.range_array_days_formated).map(function(key) {
                        return app.data.timesheet_period.range_array_days_formated[key];
                    });
                    // check if date is in your array of dates
                    function formatDate(date) {
                        var d = new Date(date),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        return [year, month, day].join('-');
                    }

                    var todaydate = app.data.timesheet_period.today;

                    function formattodayDate(todaydate) {
                        var d = new Date(todaydate),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        return [year, month, day].join('-');
                    }


                    var highlight_today = formattodayDate(todaydate);
                    if (formated == highlight_today) {
                        return [true, "ui-state-active shift_planner_datepicker_today", ''];
                    }

                    //console.log('formated='+formated);
                    if ($.inArray(formated, your_dates) != -1) {
                        // if it is return the following.
                        return [true, 'ui-state-active', ''];
                    } else {
                        // default
                        return [true, '', ''];
                    }
                }
            });

            $("#timesheet_2w_change_period").mouseenter(function() {
                $(this).addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
            }).mouseleave(function() {
                var constantlychecking_if_timer = setInterval(function() {
                    var terminatedatepicker_visiblecheck = $('.period_datepicker').datepicker("widget").is(":visible");
                    if (terminatedatepicker_visiblecheck == true) {
                        $("#timesheet_2w_change_period").addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
                    } else {
                        $("#timesheet_2w_change_period").addClass('btn-white').addClass('grey_placeholder').removeClass('btn-primary').removeClass('white_placeholder');
                        clearInterval(constantlychecking_if_timer);
                    }
                }, 100);
            });

            var resizeTimer;
            var scrollTimer;
            $(window).scroll(function() {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $(window).resize(function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    NProgress.start();
                }   
            });
        },
        timesheet_period_ajax_cache: function() {
            console.log('timesheet_period_ajax_cache');
            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    NProgress.start();
                }
            });

            var periodControl = '<div class="row left previous-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-left previous-two-weeks" ' +
                'aria-hidden="true" title="Previous Two Weeks" data-url="/prev_two_weeks_ajax"></span></div>' +
                '<div class="row right next-surround img-circle">' +
                '<span class="glyphicon glyphicon-chevron-right next-two-weeks" ' +
                'aria-hidden="true" title="Next Two Weeks" data-url="/next_two_weeks_ajax"></span></div>';

            $('#main').append(periodControl);

            if (!app.data.timesheet_period) {
                app.data.timesheet_period = [];
                app.data.timesheet_period.canNextTwoWeeks = false;
                app.data.timesheet_period.canPrevTwoWeeks = false;
            }
            if (!app.data.timesheet_period.canNextTwoWeeks) {
                $('.next-two-weeks').toggleClass('disabled');
                //
            }
            if (!app.data.timesheet_period.canPrevTwoWeeks) {
                $('.previous-two-weeks').toggleClass('disabled');
                //
            }

            $('.next-two-weeks, .previous-two-weeks').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
                /** new method to change the period without reloading the page just the table. */
                /** is it possible to ajax to the url instead modify the cache then trigger the ajax reload. which take the new cache value */
                var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
                app.ajax.jsonGET($(this).data('url'), data, null, function() {
                    NProgress.start();
                    app.util.fullscreenloading_start();
                    $('#twoweektimesheetshiftplannerInfo').css('display', 'none');
                    //console.log(app.ajax.result);
                    success_ajax_then_refresh = app.ajax.result.success;
                    if (app.ajax.result.success == true) {
                        /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
                        /** the headers are also in need of */
                        //$('#twoweektimesheetshiftplannerInfo').html('');
                        window.twoweektimesheetshiftplannertable.destroy();

                        app_attendance.ajaxexample2tb2.profile.get_twoweektimesheetshiftplannerInfo_tab();
                        var onlycallonce = 1;
                        $(document).ajaxStop(function() {
                            if (onlycallonce == 1) {
                                //console.log("All AJAX requests completed");
                                app_attendance.ajaxexample2tb2.profile.add_edit_two_week_timesheet_shift_planner_table();
                                onlycallonce = 2;
                                NProgress.done();
                                app.util.fullscreenloading_end();
                                $('#twoweektimesheetshiftplannerInfo').css('display', 'block');
                            }
                        });
                    } else {
                        /** */
                        /** */
                        //console.log('NOT working');
                        NProgress.done();
                        app.util.fullscreenloading_end();
                    }
                });
            });

            $('.glyphicon-chevron-right, .glyphicon-chevron-left, .bfh-flag-VN, .bfh-flag-GB').click(function() {
                if ($(this).hasClass('disabled')) {
                    return false
                }
            });

            var prefered_dateFormat = null;
            var patt = null;
            var prefered_dateFormat_placeholder = null;
            var number_format_per_locale = null;
            var delimiter_for_splitting_variable = null;
            var numberformat_locale = null;
            if (app.data.locale === 'vi') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'en') {
                prefered_dateFormat = "dd/mm/yy";
                prefered_dateFormat_placeholder = "dd/mm/yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '/';
            } else if (app.data.locale === 'de') {
                prefered_dateFormat = "dd.mm.yy";
                prefered_dateFormat_placeholder = "dd.mm.yyyy";
                patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                delimiter_for_splitting_variable = '.';
            }

            if (app.data.currency == 'VND') {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            } else if (app.data.currency == 'USD') {
                number_format_per_locale = '$ #,##0.00';
                numberformat_locale = 'en';
            } else if (app.data.currency == 'EUR') {
                number_format_per_locale = '€ #,##0.00';
                numberformat_locale = 'de';
            } else {
                number_format_per_locale = '#,##0.## ₫';
                numberformat_locale = 'vi';
            }

            var counting_ctd = 1;
            var $period_datepicker = $('.period_datepicker');
            $period_datepicker.datepicker({
                dateFormat: prefered_dateFormat,
                showMonthAfterYear: true,
                numberOfMonths: 2,
                changeMonth: true,
                changeYear: true,
                yearRange: "-10:+1",
                showOtherMonths: false,
                selectOtherMonths: true,
                toggleActive: true,
                todayHighlight: false,
                minDate: "-10y",
                maxDate: "+1y",
                autoclose: true,
                defaultDate: null,
                onSelect: function() {
                    /**you need to format the date before sending */
                    /** from locale to expected YYYY-MM-DD */
                    var theselecteddate = $('.period_datepicker').val();
                    //console.log(theselecteddate);
                    var from = theselecteddate.split(delimiter_for_splitting_variable);
                    if (app.data.locale === 'vi') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    } else if (app.data.locale === 'en') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    } else if (app.data.locale === 'de') {
                        var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                    }
                    //console.log(datetogoto);
                    //console.log(app.data.timesheet_period.gotoTwoWeeksUrl);
                    /** new method to be able to change the period without reloading the page */
                    //window.location = app.data.timesheet_period.gotoTwoWeeksUrl + '/' + datetogoto;
                    var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
                    app.ajax.jsonGET(app.data.timesheet_period.gotoTwoWeeksUrl + '/' + datetogoto, data, null, function() {
                        NProgress.start();
                        app.util.fullscreenloading_start();
                        $('#twoweektimesheetshiftplannerInfo').css('display', 'none');
                        //console.log(app.ajax.result);
                        success_ajax_then_refresh = app.ajax.result.success;
                        if (app.ajax.result.success == true) {
                            /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
                            /** the headers are also in need of */
                            //$('#twoweektimesheetshiftplannerInfo').html('');
                            window.twoweektimesheetshiftplannertable.destroy();

                            app_attendance.ajaxexample2tb2.profile.get_twoweektimesheetshiftplannerInfo_tab();
                            var onlycallonce = 1;
                            $(document).ajaxStop(function() {
                                if (onlycallonce == 1) {
                                    //console.log("All AJAX requests completed");
                                    app_attendance.ajaxexample2tb2.profile.add_edit_two_week_timesheet_shift_planner_table();
                                    onlycallonce = 2;
                                    NProgress.done();
                                    app.util.fullscreenloading_end();
                                    $('#twoweektimesheetshiftplannerInfo').css('display', 'block');
                                }
                            });

                        } else {
                            /** */
                            /** */
                            //console.log('NOT working');
                            NProgress.done();
                            app.util.fullscreenloading_end();
                        }
                    });
                },
                onClose: function() {
                    var addressinput = $(this).val();
                    /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                    var res = patt.test(addressinput);

                    // if (res == true) {
                    //     $('#contract_date').closest('td').removeClass('has-error');
                    //     $('#contract_date').nextAll('.help-block').css('display', 'none');

                    //     $(this).blur();
                    //     counting_ctd = 1;
                    // } else {
                    //     $("#contract_date").closest("td").addClass("has-error");
                    //     if (counting_ctd == 1) {
                    //         $("#contract_date").after('<span class="help-block"><strong>' + eval("app.translations."+app.data.locale+".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations."+app.data.locale+".or_choose_from_the_calendar") + '</strong></span>');
                    //     } else {
                    //         // it exists
                    //     }
                    //     counting_ctd++;
                    //     $(this).blur();
                    // }
                },
                beforeShow: function(input, obj) {
                    // $period_datepicker.after($period_datepicker.datepicker('widget'));
                    var the_input_top = $('.period_datepicker').offset().top;
                    var the_input_left = $('.period_datepicker').offset().left;
                    setTimeout(function() {
                        $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                        $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                    }, 0);
                },
                onChangeMonthYear: function() {
                    var the_input_top = $('.period_datepicker').offset().top;
                    var the_input_left = $('.period_datepicker').offset().left;
                    setTimeout(function() {
                        $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                        $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                    }, 0);
                },
                beforeShowDay: function(date) {
                    var formated = formatDate(date);
                    var your_dates = Object.keys(app.data.timesheet_period.range_array_days_formated).map(function(key) {
                        return app.data.timesheet_period.range_array_days_formated[key];
                    });
                    // check if date is in your array of dates
                    function formatDate(date) {
                        var d = new Date(date),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        return [year, month, day].join('-');
                    }

                    var todaydate = app.data.timesheet_period.today;

                    function formattodayDate(todaydate) {
                        var d = new Date(todaydate),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        return [year, month, day].join('-');
                    }


                    var highlight_today = formattodayDate(todaydate);
                    if (formated == highlight_today) {
                        return [true, "ui-state-active shift_planner_datepicker_today", ''];
                    }

                    //console.log('formated='+formated);
                    if ($.inArray(formated, your_dates) != -1) {
                        // if it is return the following.
                        return [true, 'ui-state-active', ''];
                    } else {
                        // default
                        return [true, '', ''];
                    }
                }
            });

            $("#timesheet_2w_change_period").mouseenter(function() {
                $(this).addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
            }).mouseleave(function() {
                var constantlychecking_if_timer = setInterval(function() {
                    var terminatedatepicker_visiblecheck = $('.period_datepicker').datepicker("widget").is(":visible");
                    if (terminatedatepicker_visiblecheck == true) {
                        $("#timesheet_2w_change_period").addClass('btn-primary').addClass('white_placeholder').removeClass('btn-white').removeClass('grey_placeholder');
                    } else {
                        $("#timesheet_2w_change_period").addClass('btn-white').addClass('grey_placeholder').removeClass('btn-primary').removeClass('white_placeholder');
                        clearInterval(constantlychecking_if_timer);
                    }
                }, 100);
            });

            var resizeTimer;
            var scrollTimer;
            $(window).scroll(function() {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $(window).resize(function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    $('.period_datepicker').datepicker("hide").blur();
                }, 0);
            });
            $('.button').not('.isDisabled').click(function() {
                if($(this).attr("target") !== "_blank"){
                    app.util.fullscreenloading_start();
                    NProgress.start();
                }                 
            });
        },
        showHideBackdrop: function() {
            $('#backdrop').toggleClass('hidden');
            return false;
        }
    },
    ajaxed_notifications: {
        index: {
            full_scale_logout: function() {
                // if (app.data.locale == 'vi') {
                //     console.log("%cGiống như nhìn dưới mui xe? Chúng tôi đang thuê những người như bạn! Hãy đến và làm việc cho chúng tôi: https://br24.com/en/career/", 'color: #4fa6d3;font:18px/80px "DM Sans", sans-serif;');
                // } else {
                //     console.log("%cLike looking under the hood? We’re hiring people like you! Come and work for us: https://br24.com/en/career/", 'color: #4fa6d3;font:18px/80px "DM Sans", sans-serif;');
                // }

                //when the user has logged out of one tab then it ripple effects the local storage telling that they have logged out. 
                //thus makes the tab refresh logging them out to redirect to the login page to windows that are in the background..
                $('#logout').click(function(event) {
                    localStorage.setItem('logout-event', 'logout' + Math.random());
                });
                window.addEventListener('storage', function(event) {
                    if (event.key == 'logout-event') {
                        setTimeout(function() {
                            window.location.reload();
                            localStorage.removeItem('logout-event');
                            for (var i = 0, len = localStorage.length; i < len; ++i) {
                                if(localStorage.key(i) !== null){
                                    //console.log(localStorage.key(i) + ": " + localStorage.getItem(localStorage.key(i)));
                                    var localStorage_KEY_name_check = localStorage.key(i).indexOf("Br24_");
                                    //console.log(localStorage_KEY_name_check);
                                    if (localStorage_KEY_name_check >= 0) {
                                        localStorage.removeItem(localStorage.key(i));
                                        /** when you remove an item the indexes will all shift down so now you have to instruct the foreach to compensate */
                                        i = i - 1;
                                    }
                                }
                            }
                            sessionStorage.removeItem('XSRF-TOKEN');
                        }, 1000);
                    }
                });

                /** add to site the xhr listener for app control */
                $(function() {
                    $.xhrPool = [];
                    window.aklsdf = [];
                    $.xhrPool.abortAll = function() {
                        /**console.log('aborting each xhrPool');*/
                        $(this).each(function(i, jqXHR) { /**  cycle through list of recorded connection */
                            jqXHR.abort(); /**  aborts connection */
                            $.xhrPool.splice(i, 1); /**  removes from list by index */
                        });
                    }
                    $.ajaxSetup({
                        beforeSend: function(jqXHR) {
                            $.xhrPool.push(jqXHR); /**  add connection to list */
                            window.aklsdf.push(jqXHR);
                        },
                        complete: function(jqXHR) {
                            var i = $.xhrPool.indexOf(jqXHR); /**  get index for current connection completed */
                            if (i > -1) {
                                $.xhrPool.splice(i, 1); /**  removes from list by index */
                                window.aklsdf.splice(i, 1);
                            }

                        }
                    });
                });

                window.addEventListener('keydown', function(e) {
                    if ((e.which || e.keyCode) == 116 && e.shiftKey == true){
                        /** special reload page ignoring cached resources */
                        for (var i = 0, len = localStorage.length; i <= len; ++i) {
                            if(localStorage.key(i) !== null){
                                /**console.log(localStorage.key(i) + ": " + localStorage.getItem(localStorage.key(i)));*/
                                var localStorage_KEY_name_check = localStorage.key(i).indexOf("Br24_");
                                /**console.log(localStorage_KEY_name_check);*/
                                if (localStorage_KEY_name_check >= 0) {
                                    localStorage.removeItem(localStorage.key(i));
                                    /** when you remove an item the indexes will all shift down so now you have to instruct the foreach to compensate */
                                    i = i - 1;
                                }
                            }
                        }
                    }
                    if (e.keyCode == 27) {

                        /** on esc key */
                        /** want to hide the assignees drop down */
                        $('.is_br24_employee').selectize().blur();
                        $('.track').css('display', 'none');
                        $('.color').css('z-index', '');

                        app.util.fullscreenloading_end();
                        app.util.nprogressdone();
                        $.xhrPool.abortAll();
                        /** need a special case for when ajax are currently running */
                        if (window.aklsdf.length < 1 || window.aklsdf == undefined) {
                            /** no currently running ajax requests */
                        } else {
                            if ($.colorbox != undefined) {
                                $.colorbox.close();
                            }
                        }
                    }
                }, false);

                /** turn download links to preview links when key combination and click is made */
                $(document).ready(function() {
                    window.addEventListener("mousedown", function(event) {
                        $("a").mousedown(function(event) {
                            if (event.ctrlKey == true && event.shiftKey == true && event.altKey == true) {
                                // console.log('link with shift mouse down');
                                if (event.target.href !== undefined) {
                                    if (event.target.href.indexOf("/download/") >= 0 || event.target.href.indexOf("/download_old_contract/") >= 0) {
                                        if (event.target.href.indexOf("&preview_only=true") >= 0) {
                                            // console.log('It has already url variable down nothing');
                                        } else {
                                            var _href = event.target.href;
                                            $(this).attr("href", _href + '&preview_only=true');
                                            // console.log('href changed');
                                        }
                                    }
                                }
                            }
                        }).mouseup(function(event) {
                            // console.log('link with shift mouse up');
                        }).mouseleave(function(event) {
                            /**console.log(event);*/
                            // console.log('link with shift mouse leave');
                            if (event.target.href !== undefined) {
                                if (event.target.href.indexOf("/download/") >= 0 || event.target.href.indexOf("/download_old_contract/") >= 0) {
                                    if (event.target.href.indexOf("&preview_only=true") >= 0) {
                                        var _href = event.target.href;
                                        var modified_href = _href.replace('&preview_only=true', '');
                                        $(this).attr("href", modified_href);
                                        // console.log('href_changed_back');
                                    }
                                }
                            }
                        });
                    }, false);
                });



                $(window).ajaxSuccess(function(event, request, settings) {
                    var checkauthenticatedcheck1 = request.responseText.indexOf('<h3>Welcome to Br24 Employee Tool</h3>');
                    var checkauthenticatedcheck2 = request.responseText.indexOf('<meta name="loginpage" content="loginpage">');
                    if (checkauthenticatedcheck1 >= 0 && checkauthenticatedcheck2 >= 0) {
                        $("body").remove();
                        localStorage.setItem('logout-event', 'logout' + Math.random());
                        window.location.reload();
                    }
                });

                window.addEventListener("pageshow", function(event) {
                    //console.log(event);
                    //console.log(window.performance);
                    var historyTraversal_type1 = (typeof window.performance != "undefined" && window.performance.navigation.type === 1);
                    var historyTraversal_type2 = (typeof window.performance != "undefined" && window.performance.navigation.type === 2);
                    if (event.persisted == true) {
                        window.location.reload();
                    }
                    if (historyTraversal_type1) {
                        /** Handle page restore. */
                        /** console.log('refresh'); */
                        /** window.location.reload(); */
                    }
                    if (event.persisted == false && historyTraversal_type2) {
                        /** Handle page restore. */
                        /** console.log('clicking back or forwards something'); */
                        window.location.reload();
                    }
                    /** 0 = new page; */
                    /** 1 = reloaded same page */
                    /** 2 = traverse back or forwards */
                    if (window.performance && window.performance.navigation.type == window.performance.navigation.TYPE_BACK_FORWARD) {
                        window.location.reload();
                    }
                });
            },
            pusher_notification_initialised_employee: function() {
                /** Enable pusher logging - don't include this in production */
                //Pusher.logToConsole = false;
                /** configure pusher plugin */
                // var pusher = new Pusher('', {
                //     encrypted: false,
                //     cluster: 'ap1'
                // });
                /** Subscribe to the channel we specified in our Laravel Event */
                //var channel = pusher.subscribe('employee-initialised');
                /** Bind a function to a Event (the full Laravel class) */
                // channel.bind('App\\Events\\NewEmployeeInitialisedEvent', function(data) {
                //     //console.log('thedatareceived=' + JSON.stringify(data));
                //     var today = new Date();
                //     var dd = today.getDate();
                //     var mm = today.getMonth() + 1;
                //     //January is 0! 
                //     var yyyy = today.getFullYear();
                //     if (dd < 10) {
                //         dd = '0' + dd
                //     }
                //     if (mm < 10) {
                //         mm = '0' + mm
                //     }
                //     var today = dd + '/' + mm + '/' + yyyy;

                //     // Create a new JavaScript Date object based on the timestamp
                //     var ts = Math.round((new Date()).getTime() / 1000);
                //     // multiplied by 1000 so that the argument is in milliseconds, not seconds.
                //     var date = new Date(ts * 1000);
                //     // Hours part from the timestamp
                //     var hours = date.getHours();
                //     // Minutes part from the timestamp
                //     var minutes = "0" + date.getMinutes();
                //     // Seconds part from the timestamp
                //     var seconds = "0" + date.getSeconds();

                //     // Will display time in 10:30:23 format
                //     var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

                //     var existingNotifications = $('#newEmployeeInit_Modal .modal-body').html();
                //     //console.log('exisitingNoti='+existingNotifications);
                //     var avatar = Math.floor(Math.random() * (71 - 20 + 1)) + 20;

                //     var newNotificationHtml = '<li class="notification active"><div class="alert_warning"><span class="noti_closebtn"' +
                //         'onClick="this.parentNode.parentNode.removeChild(this.parentNode);">&times;</span>' +
                //         '<div class="media"><div class="media-left"><div class="media-object"><img src="https://api.adorable.io/avatars/71/' + avatar +
                //         '.png" class="img-circle" alt="50x50" style="width: 50px; height: 50px;">' +
                //         '</div></div><div class="media-body"><strong class="notification-title">' + data.message +
                //         '</strong><div class="notification-meta"><small class="timestamp">' + today + ' ' + formattedTime +
                //         '</small></div></div></div></li>';
                //     //console.log(newNotificationHtml);
                //     $('#newEmployeeInit_Modal .modal-body').html(newNotificationHtml + existingNotifications);
                //     app.util.newEmployeeInit_Modal();
                // });
            },
            initialise_employee_when_blur_focus: function() {
                //this will be placed in the app.twig file to happen on every page in the background
                var window_focus = true;
                $(window).focus(function() { window_focus = true; }).blur(function() { window_focus = false; });

                var focus_has_been_out = null
                var helper_IntervalId = null;
                var helper_timer = function() {
                    helper_IntervalId = setInterval(function() {
                        //console.log('this window has focus= ' + window_focus);
                    }, 500);
                };
                //start helper timer
                helper_timer();

                /** everytime the app user goes away from focus and back get the latest list of initialised employees */
                var which_page_are_we_looking_at_timer = setInterval(function() {
                    //console.log('ticking');
                    //we check that they are seeing the window
                    if (window_focus == true) {
                        //we check they they have gone out of the window focus.
                        //if they have this part gets triggered once.
                        if (focus_has_been_out !== null) {
                            //console.log('before-ajaxing');
                            var data = {};
                            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() } });
                            //this will perfom the action to check the db likeness.. hopefully so that when the HR use the app it is the most recent.. 
                            //so i can keep the auto script to run every five minutes instead of every minute
                            var ajaxresult = '';
                            app.ajax.json('/newEmployeeInitialised', data, null, function() {
                                ajaxresult = app.ajax.result;
                                //console.log(JSON.stringify(ajaxresult));
                                //console.log('back --- we have restults');
                                //if there is no result it keeps atempting to go to the route
                                clearInterval(helper_IntervalId);
                                helper_IntervalId = null;

                                //after getting results we reset the check for whether they have gone out of focus of the window for the next time they come back
                            });
                            focus_has_been_out = null;
                            //console.log('reset_timer_after_getting_result');
                            if (helper_IntervalId == null) {
                                helper_timer();
                            }
                        }
                    }
                    if (window_focus == false) {
                        //console.log('timer-reset');
                        focus_has_been_out = 1;
                        if (helper_IntervalId == null) {
                            helper_timer();
                        }
                    };
                }, 1000);
            },
            initialise_employee_once_when_at_home: function() {
                $(document).ready(function() {
                    var navtohometab = window.location.href.indexOf("/home");
                    if (navtohometab >= 0) {
                        //console.log('at home');
                        //console.log('doing once_right now!!!');
                        var data = {};
                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() } });
                        //this will perfom the action to check the db likeness.. hopefully so that when the HR use the app it is the most recent.. 
                        //so i can keep the auto script to run every five minutes instead of every minute
                        app.ajax.json('/newEmployeeInitialised', data, null, function() {
                            var ajaxresult = app.ajax.result;
                            //console.log('initialise_employee_once_when_at_home=' + JSON.stringify(ajaxresult));
                        });
                        //should not be able to go to other areas of the app until this finishes. other wise the tables will error.
                    }
                });
            },
            auto_fade_out_alert_dismissable: function() {
                /** ONLY when coming back from a long import process don't auto hide the alert dismissable */
                var doc_refferer = document.referrer;
                //console.log('documentreferrerURL=' + doc_refferer);
                var navfrom_importURL = doc_refferer.indexOf("/import/");
                //console.log('navfrom_importURL did keyword contain /import/ ? =' + navfrom_importURL);
                if (navfrom_importURL >= 0) {
                    //console.log('coming from');
                } else {
                    //console.log('not coming from');
                    $(document).ready(function() {
                        var constantlychecking_if_timer_for_alert_dissmissable = setInterval(function() {
                            var alert_dismissiable_isvisible_check = $('.alert-dismissable').is(":visible");
                            if (alert_dismissiable_isvisible_check == true) {
                                $.when($('.alert-dismissable').delay(5000).fadeOut(500)).done(function() {
                                    $('.alert-dismissable').css('display', "none");
                                });
                            }
                        }, 500);
                    });
                }
            },
            fixed_nav_header_on_scroll: function() {
                var mainbody = $('#main');
                var mainbody_padding = $('.visitor').outerHeight(true);
                /** try to get this in place for these on scrol or on resize function*/
                var remembering_mainbody_padding = '';
                if (remembering_mainbody_padding != mainbody_padding) {
                    remembering_mainbody_padding = mainbody_padding;
                }
                //console.log("mainbody_padding=" + mainbody_padding);
                $(document).ready(function() {
                    mainbody.css('padding-top', mainbody_padding);
                });
                $(window).scroll(function() {
                    /** stick the main header to the top */
                    var sticky = $('.sticky-header');
                    var scroll = $(window).scrollTop();
                    mainbody_padding = $('.visitor').outerHeight(true);
                    if (scroll >= 0) {
                        sticky.addClass('fixed-header-on-scroll');
                        mainbody.css('padding-top', mainbody_padding);
                    } else {
                        sticky.removeClass('fixed-header-on-scroll');
                        mainbody.css('padding-top', '');
                    }

                    /** on scroll hide the drop down menus for multiselect */
                    var multi_select_option_list = $('.ms-options-wrap');
                    multi_select_option_list.removeClass('ms-active');
                });
                $(window).resize(function() {
                    mainbody_padding = $('.visitor').outerHeight(true);
                    mainbody.css('padding-top', mainbody_padding);
                });
            },
            constantly_check_logged_in_status: function() {
                /** the idea is that if they were logged in and for whatever reason */
                /** cache gets cleared the other tabs should go back to the login page rather than sticking around */
                //console.log('checkingifloggedinstatus');
            },
        }
    },
};
