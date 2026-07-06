<?php

namespace App\Controllers;

use App\Models\AntennaModeModel;
use App\Models\DepartmentModel;
use App\Models\JobPositionModel;
use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\OperatingHourModel;
use App\Models\ShiftModel;
use App\Models\StaffGroupModel;
use App\Models\PublicHolidayModel;
use App\Models\GroupsShiftModel;
use App\Models\StaffAvailabilityModel;
use App\Models\StaffShiftAllocationModel;
use App\Models\LeaveReasonModel;
use App\Models\UnitOfMeasureModel;
use App\Models\SupplierModel;
use App\Models\WorkerModel;

class Config extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Configuration',
            'user' => $this->getLoggedInUser()
        ];

        return view('config/index', $data);
    }
    
    public function rfidSettings()
    {
        $config = config('RFIDReader');
        
        $data = [
            'title' => 'RFID Settings',
            'user' => $this->getLoggedInUser(),
            'config' => $config
        ];

        return view('config/rfid_settings', $data);
    }
    
    public function updateRfidSettings()
    {
        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'checkInToCheckOutInterval' => 'required|integer|greater_than[0]',
            'checkOutToCheckInInterval' => 'required|integer|greater_than[0]',
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->with('error', 'Invalid input values. Please enter positive integers.');
        }
        
        $checkInToCheckOut = (int) $this->request->getPost('checkInToCheckOutInterval');
        $checkOutToCheckIn = (int) $this->request->getPost('checkOutToCheckInInterval');
        
        // Read the config file
        $configPath = APPPATH . 'Config/RFIDReader.php';
        $content = file_get_contents($configPath);
        
        // Update the values
        $content = preg_replace(
            '/public int \$checkInToCheckOutInterval = \d+;/',
            'public int $checkInToCheckOutInterval = ' . $checkInToCheckOut . ';',
            $content
        );
        
        $content = preg_replace(
            '/public int \$checkOutToCheckInInterval = \d+;/',
            'public int $checkOutToCheckInInterval = ' . $checkOutToCheckIn . ';',
            $content
        );
        
        // Write back to file
        if (file_put_contents($configPath, $content)) {
            return redirect()->to(base_url('config/rfid-settings'))->with('success', 'RFID settings updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to update configuration file.');
        }
    }

    public function widgetSettings()
    {
        $data = [
            'title' => 'Widget Settings',
            'user' => $this->getLoggedInUser(),
            'config' => config('Widgets'),
        ];

        return view('config/widget_settings', $data);
    }

    public function updateWidgetSettings()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'floatingButtonSize' => 'required|in_list[sm,md,lg]',
            'panelSize' => 'required|in_list[sm,md,lg]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->with('error', 'Invalid widget settings.');
        }

        $floatingButtonSize = $this->request->getPost('floatingButtonSize');
        $panelSize = $this->request->getPost('panelSize');
        $floatingButtonsMoveable = $this->request->getPost('floatingButtonsMoveable') === '1';
        $panelsMoveable = $this->request->getPost('panelsMoveable') === '1';

        $configPath = APPPATH . 'Config/Widgets.php';
        $content = file_get_contents($configPath);

        $content = preg_replace(
            '/public bool \$floatingButtonsMoveable = (true|false);/',
            'public bool $floatingButtonsMoveable = ' . ($floatingButtonsMoveable ? 'true' : 'false') . ';',
            $content
        );
        $content = preg_replace(
            '/public bool \$panelsMoveable = (true|false);/',
            'public bool $panelsMoveable = ' . ($panelsMoveable ? 'true' : 'false') . ';',
            $content
        );
        $content = preg_replace(
            '/public string \$floatingButtonSize = \'(sm|md|lg)\';/',
            "public string \$floatingButtonSize = '" . $floatingButtonSize . "';",
            $content
        );
        $content = preg_replace(
            '/public string \$panelSize = \'(sm|md|lg)\';/',
            "public string \$panelSize = '" . $panelSize . "';",
            $content
        );

        if ($content === null || $content === false) {
            return redirect()->back()->with('error', 'Failed to update configuration file.');
        }

        if (!preg_match('/public string \$floatingButtonSize = \'' . preg_quote($floatingButtonSize, '/') . '\';/', $content)
            || !preg_match('/public string \$panelSize = \'' . preg_quote($panelSize, '/') . '\';/', $content)) {
            return redirect()->back()->with('error', 'Failed to apply widget size settings.');
        }

        if (file_put_contents($configPath, $content)) {
            return redirect()->to(base_url('config/widget-settings'))->with('success', 'Widget settings updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to update configuration file.');
    }
    
    public function antennaMode()
    {
        $antennaModeModel = new AntennaModeModel();
        
        $data = [
            'title' => 'Antenna Mode List',
            'user' => $this->getLoggedInUser(),
            'modes' => $antennaModeModel->orderBy('mode_name', 'ASC')->findAll()
        ];

        return view('config/antenna_mode', $data);
    }
    
    public function storeantennaMode()
    {
        $antennaModeModel = new AntennaModeModel();
        
        $data = [
            'mode_name' => $this->request->getPost('mode_name'),
            'description' => $this->request->getPost('description'),
            'color' => $this->request->getPost('color'),
            'is_active' => 1,
        ];
        
        if ($antennaModeModel->insert($data)) {
            return redirect()->to(base_url('config/antenna-mode'))->with('success', 'Antenna mode added successfully!');
        } else {
            $errors = $antennaModeModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add antenna mode.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateAntennaMode()
    {
        $antennaModeModel = new AntennaModeModel();
        
        $id = $this->request->getPost('id');
        
        // Set validation rules that exclude current ID for uniqueness check
        $antennaModeModel->setValidationRule('mode_name', 'required|min_length[2]|max_length[100]|is_unique[antenna_modes.mode_name,id,' . $id . ']');
        
        $data = [
            'mode_name' => $this->request->getPost('mode_name'),
            'description' => $this->request->getPost('description'),
            'color' => $this->request->getPost('color'),
        ];
        
        if ($antennaModeModel->update($id, $data)) {
            return redirect()->to(base_url('config/antenna-mode'))->with('success', 'Antenna mode updated successfully!');
        } else {
            $errors = $antennaModeModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update antenna mode.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleAntennaMode($id)
    {
        $antennaModeModel = new AntennaModeModel();
        $mode = $antennaModeModel->find($id);
        
        if ($mode) {
            $antennaModeModel->update($id, ['is_active' => !$mode['is_active']]);
            return redirect()->to(base_url('config/antenna-mode'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/antenna-mode'))->with('error', 'Antenna mode not found.');
    }
    
    public function deleteAntennaMode($id)
    {
        $antennaModeModel = new AntennaModeModel();
        
        if ($antennaModeModel->delete($id)) {
            return redirect()->to(base_url('config/antenna-mode'))->with('success', 'Antenna mode deleted successfully!');
        }
        
        return redirect()->to(base_url('config/antenna-mode'))->with('error', 'Failed to delete antenna mode.');
    }
    
    // Department Management
    public function departments()
    {
        $departmentModel = new DepartmentModel();
        
        $data = [
            'title' => 'Department List',
            'user' => $this->getLoggedInUser(),
            'departments' => $departmentModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('config/departments', $data);
    }
    
    public function storeDepartment()
    {
        $departmentModel = new DepartmentModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($departmentModel->insert($data)) {
            return redirect()->to(base_url('config/departments'))->with('success', 'Department added successfully!');
        } else {
            $errors = $departmentModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add department.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateDepartment()
    {
        $departmentModel = new DepartmentModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($departmentModel->update($id, $data)) {
            return redirect()->to(base_url('config/departments'))->with('success', 'Department updated successfully!');
        } else {
            $errors = $departmentModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update department.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleDepartment($id)
    {
        $departmentModel = new DepartmentModel();
        $department = $departmentModel->find($id);
        
        if ($department) {
            $departmentModel->update($id, [
                'is_active' => !$department['is_active'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->to(base_url('config/departments'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/departments'))->with('error', 'Department not found.');
    }
    
    public function deleteDepartment($id)
    {
        $departmentModel = new DepartmentModel();
        
        if ($departmentModel->delete($id)) {
            return redirect()->to(base_url('config/departments'))->with('success', 'Department deleted successfully!');
        }
        
        return redirect()->to(base_url('config/departments'))->with('error', 'Failed to delete department.');
    }
    
    // Job Position Management
    public function jobPositions()
    {
        $jobPositionModel = new JobPositionModel();
        
        $data = [
            'title' => 'Job Position List',
            'user' => $this->getLoggedInUser(),
            'positions' => $jobPositionModel->orderBy('title', 'ASC')->findAll()
        ];

        return view('config/job_positions', $data);
    }
    
    public function storeJobPosition()
    {
        $jobPositionModel = new JobPositionModel();
        
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($jobPositionModel->insert($data)) {
            return redirect()->to(base_url('config/job-positions'))->with('success', 'Job position added successfully!');
        } else {
            $errors = $jobPositionModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add job position.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateJobPosition()
    {
        $jobPositionModel = new JobPositionModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($jobPositionModel->update($id, $data)) {
            return redirect()->to(base_url('config/job-positions'))->with('success', 'Job position updated successfully!');
        } else {
            $errors = $jobPositionModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update job position.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleJobPosition($id)
    {
        $jobPositionModel = new JobPositionModel();
        $position = $jobPositionModel->find($id);
        
        if ($position) {
            $jobPositionModel->update($id, [
                'is_active' => !$position['is_active'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->to(base_url('config/job-positions'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/job-positions'))->with('error', 'Job position not found.');
    }
    
    public function deleteJobPosition($id)
    {
        $jobPositionModel = new JobPositionModel();
        
        if ($jobPositionModel->delete($id)) {
            return redirect()->to(base_url('config/job-positions'))->with('success', 'Job position deleted successfully!');
        }
        
        return redirect()->to(base_url('config/job-positions'))->with('error', 'Failed to delete job position.');
    }
    
    // Location Management - States
    public function states()
    {
        $stateModel = new StateModel();
        $countryModel = new CountryModel();
        
        $data = [
            'title' => 'State List',
            'user' => $this->getLoggedInUser(),
            'states' => $stateModel->getStatesWithCountry(),
            'countries' => $countryModel->getActiveCountries()
        ];

        return view('config/states', $data);
    }
    
    public function storeState()
    {
        $stateModel = new StateModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'country_id' => $this->request->getPost('country_id'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($stateModel->insert($data)) {
            return redirect()->to(base_url('config/states'))->with('success', 'State added successfully!');
        } else {
            $errors = $stateModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add state.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateState()
    {
        $stateModel = new StateModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'country_id' => $this->request->getPost('country_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($stateModel->update($id, $data)) {
            return redirect()->to(base_url('config/states'))->with('success', 'State updated successfully!');
        } else {
            $errors = $stateModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update state.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleState($id)
    {
        $stateModel = new StateModel();
        $state = $stateModel->find($id);
        
        if ($state) {
            $stateModel->update($id, [
                'is_active' => !$state['is_active'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->to(base_url('config/states'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/states'))->with('error', 'State not found.');
    }
    
    public function deleteState($id)
    {
        $stateModel = new StateModel();
        
        if ($stateModel->delete($id)) {
            return redirect()->to(base_url('config/states'))->with('success', 'State deleted successfully!');
        }
        
        return redirect()->to(base_url('config/states'))->with('error', 'Failed to delete state.');
    }
    
    // Location Management - Cities
    public function cities()
    {
        $cityModel = new CityModel();
        $countryModel = new CountryModel();
        $stateModel = new StateModel();
        
        $data = [
            'title' => 'City List',
            'user' => $this->getLoggedInUser(),
            'cities' => $cityModel->getCitiesWithDetails(),
            'countries' => $countryModel->getActiveCountries(),
            'states' => $stateModel->getActiveStates()
        ];

        return view('config/cities', $data);
    }
    
    public function storeCity()
    {
        $cityModel = new CityModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'state_id' => $this->request->getPost('state_id'),
            'country_id' => $this->request->getPost('country_id'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($cityModel->insert($data)) {
            return redirect()->to(base_url('config/cities'))->with('success', 'City added successfully!');
        } else {
            $errors = $cityModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add city.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateCity()
    {
        $cityModel = new CityModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'state_id' => $this->request->getPost('state_id'),
            'country_id' => $this->request->getPost('country_id'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($cityModel->update($id, $data)) {
            return redirect()->to(base_url('config/cities'))->with('success', 'City updated successfully!');
        } else {
            $errors = $cityModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update city.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleCity($id)
    {
        $cityModel = new CityModel();
        $city = $cityModel->find($id);
        
        if ($city) {
            $cityModel->update($id, [
                'is_active' => !$city['is_active'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->to(base_url('config/cities'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/cities'))->with('error', 'City not found.');
    }
    
    public function deleteCity($id)
    {
        $cityModel = new CityModel();
        
        if ($cityModel->delete($id)) {
            return redirect()->to(base_url('config/cities'))->with('success', 'City deleted successfully!');
        }
        
        return redirect()->to(base_url('config/cities'))->with('error', 'Failed to delete city.');
    }
    
    // AJAX endpoint for getting states by country
    public function getStatesByCountry()
    {
        $stateModel = new StateModel();
        $countryId = $this->request->getGet('country_id');
        
        $states = $stateModel->getStatesByCountry($countryId);
        
        return $this->response->setJSON($states);
    }
    
    // Country Management
    public function countries()
    {
        $countryModel = new CountryModel();
        
        $data = [
            'title' => 'Country List',
            'user' => $this->getLoggedInUser(),
            'countries' => $countryModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('config/countries', $data);
    }
    
    public function storeCountry()
    {
        $countryModel = new CountryModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'code' => strtoupper($this->request->getPost('code')),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($countryModel->insert($data)) {
            return redirect()->to(base_url('config/countries'))->with('success', 'Country added successfully!');
        } else {
            $errors = $countryModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add country.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateCountry()
    {
        $countryModel = new CountryModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'code' => strtoupper($this->request->getPost('code')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($countryModel->update($id, $data)) {
            return redirect()->to(base_url('config/countries'))->with('success', 'Country updated successfully!');
        } else {
            $errors = $countryModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update country.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleCountry($id)
    {
        $countryModel = new CountryModel();
        $country = $countryModel->find($id);
        
        if ($country) {
            $countryModel->update($id, [
                'is_active' => !$country['is_active'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->to(base_url('config/countries'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/countries'))->with('error', 'Country not found.');
    }
    
    public function deleteCountry($id)
    {
        $countryModel = new CountryModel();
        
        if ($countryModel->delete($id)) {
            return redirect()->to(base_url('config/countries'))->with('success', 'Country deleted successfully!');
        }
        
        return redirect()->to(base_url('config/countries'))->with('error', 'Failed to delete country.');
    }
    
    // RFID Reader Settings
    public function rfidReader()
    {
        $configFile = APPPATH . 'Config/RFIDReader.php';
        
        // Read current settings from config file
        $rfidConfig = config('RFIDReader');
        
        $data = [
            'title' => 'RFID Reader Settings',
            'user' => $this->getLoggedInUser(),
            'config' => $rfidConfig,
            'configPath' => $configFile
        ];

        return view('config/rfid_reader', $data);
    }
    
    public function updateRfidReader()
    {
        $readerIP = $this->request->getPost('reader_ip');
        $readerPort = $this->request->getPost('reader_port');
        $readerID = $this->request->getPost('reader_id');
        $protocol = $this->request->getPost('protocol');
        $defaultZoneID = $this->request->getPost('default_zone_id');
        $connectionTimeout = $this->request->getPost('connection_timeout');
        
        // Validate inputs
        if (empty($readerIP) || empty($readerPort)) {
            return redirect()->back()->with('error', 'Reader IP and Port are required.');
        }
        
        // Validate IP address format
        if (!filter_var($readerIP, FILTER_VALIDATE_IP)) {
            return redirect()->back()->with('error', 'Invalid IP address format.');
        }
        
        // Validate port number
        if (!is_numeric($readerPort) || $readerPort < 1 || $readerPort > 65535) {
            return redirect()->back()->with('error', 'Port must be between 1 and 65535.');
        }
        
        // Read the config file
        $configFile = APPPATH . 'Config/RFIDReader.php';
        $configContent = file_get_contents($configFile);
        
        // Update the configuration values using regex
        $configContent = preg_replace(
            '/public string \$readerIP = \'[^\']*\';/',
            "public string \$readerIP = '" . $readerIP . "';",
            $configContent
        );
        
        $configContent = preg_replace(
            '/public int \$readerPort = \d+;/',
            "public int \$readerPort = " . $readerPort . ";",
            $configContent
        );
        
        $configContent = preg_replace(
            '/public string \$readerID = \'[^\']*\';/',
            "public string \$readerID = '" . $readerID . "';",
            $configContent
        );
        
        $configContent = preg_replace(
            '/public string \$protocol = \'[^\']*\';/',
            "public string \$protocol = '" . $protocol . "';",
            $configContent
        );
        
        $configContent = preg_replace(
            '/public int \$defaultZoneID = \d+;/',
            "public int \$defaultZoneID = " . $defaultZoneID . ";",
            $configContent
        );
        
        $configContent = preg_replace(
            '/public int \$connectionTimeout = \d+;/',
            "public int \$connectionTimeout = " . $connectionTimeout . ";",
            $configContent
        );
        
        // Write the updated config back to the file
        if (file_put_contents($configFile, $configContent)) {
            return redirect()->to(base_url('config/rfid-reader'))->with('success', 'RFID Reader settings updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to update configuration file. Check file permissions.');
        }
    }
    
    public function testRfidConnection()
    {
        $rfidReader = new \App\Libraries\YanzeoSA810();
        
        $connected = $rfidReader->connect();
        
        if ($connected) {
            $rfidReader->disconnect();
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Successfully connected to RFID reader!'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to connect to RFID reader. Please check IP and port settings.'
        ]);
    }
    
    // Operating Hours Methods
    public function operatingHours()
    {
        $operatingHourModel = new OperatingHourModel();
        
        $data = [
            'title' => 'Operating Hours',
            'user' => $this->getLoggedInUser(),
            'hours' => $operatingHourModel->orderBy('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')->findAll()
        ];

        return view('config/operating_hours', $data);
    }
    
    public function storeOperatingHour()
    {
        $operatingHourModel = new OperatingHourModel();
        
        $day = $this->request->getPost('day');
        $startHour = str_pad($this->request->getPost('start_hour'), 2, '0', STR_PAD_LEFT);
        $startMinute = str_pad($this->request->getPost('start_minute'), 2, '0', STR_PAD_LEFT);
        $startPeriod = $this->request->getPost('start_period');
        $endHour = str_pad($this->request->getPost('end_hour'), 2, '0', STR_PAD_LEFT);
        $endMinute = str_pad($this->request->getPost('end_minute'), 2, '0', STR_PAD_LEFT);
        $endPeriod = $this->request->getPost('end_period');
        
        // Convert 12-hour format to 24-hour format
        $startHour24 = $this->convertTo24Hour($startHour, $startPeriod);
        $endHour24 = $this->convertTo24Hour($endHour, $endPeriod);
        
        $data = [
            'day' => $day,
            'start_time' => $startHour24 . ':' . $startMinute . ':00',
            'end_time' => $endHour24 . ':' . $endMinute . ':00',
            'is_active' => 1,
        ];
        
        if ($operatingHourModel->insert($data)) {
            return redirect()->to(base_url('config/operating-hours'))->with('success', 'Operating hours added successfully!');
        } else {
            $errors = $operatingHourModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add operating hours.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateOperatingHour()
    {
        $operatingHourModel = new OperatingHourModel();
        
        $id = $this->request->getPost('id');
        $day = $this->request->getPost('day');
        $startHour = str_pad($this->request->getPost('start_hour'), 2, '0', STR_PAD_LEFT);
        $startMinute = str_pad($this->request->getPost('start_minute'), 2, '0', STR_PAD_LEFT);
        $startPeriod = $this->request->getPost('start_period');
        $endHour = str_pad($this->request->getPost('end_hour'), 2, '0', STR_PAD_LEFT);
        $endMinute = str_pad($this->request->getPost('end_minute'), 2, '0', STR_PAD_LEFT);
        $endPeriod = $this->request->getPost('end_period');
        
        // Convert 12-hour format to 24-hour format
        $startHour24 = $this->convertTo24Hour($startHour, $startPeriod);
        $endHour24 = $this->convertTo24Hour($endHour, $endPeriod);
        
        $data = [
            'day' => $day,
            'start_time' => $startHour24 . ':' . $startMinute . ':00',
            'end_time' => $endHour24 . ':' . $endMinute . ':00',
        ];
        
        if ($operatingHourModel->update($id, $data)) {
            return redirect()->to(base_url('config/operating-hours'))->with('success', 'Operating hours updated successfully!');
        } else {
            $errors = $operatingHourModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update operating hours.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleOperatingHour($id)
    {
        $operatingHourModel = new OperatingHourModel();
        $hour = $operatingHourModel->find($id);
        
        if ($hour) {
            $operatingHourModel->update($id, ['is_active' => !$hour['is_active']]);
            return redirect()->to(base_url('config/operating-hours'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/operating-hours'))->with('error', 'Operating hour not found.');
    }
    
    public function deleteOperatingHour($id)
    {
        $operatingHourModel = new OperatingHourModel();
        
        if ($operatingHourModel->delete($id)) {
            return redirect()->to(base_url('config/operating-hours'))->with('success', 'Operating hours deleted successfully!');
        }
        
        return redirect()->to(base_url('config/operating-hours'))->with('error', 'Failed to delete operating hours.');
    }
    
    // Shift Methods
    public function shifts()
    {
        $shiftModel = new ShiftModel();
        
        $data = [
            'title' => 'Shift List',
            'user' => $this->getLoggedInUser(),
            'shifts' => $shiftModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('config/shifts', $data);
    }
    
    public function storeShift()
    {
        $shiftModel = new ShiftModel();
        
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => 1,
        ];
        
        if ($shiftModel->insert($data)) {
            return redirect()->to(base_url('config/shifts'))->with('success', 'Shift added successfully!');
        } else {
            $errors = $shiftModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add shift.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateShift()
    {
        $shiftModel = new ShiftModel();
        $workerModel = new WorkerModel();
        
        $id = $this->request->getPost('id');
        $newName = $this->request->getPost('name');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        
        // Get the old shift name before updating
        $oldShift = $shiftModel->find($id);
        
        if (!$oldShift) {
            return redirect()->back()->with('error', 'Shift not found.');
        }
        
        $oldName = $oldShift['name'];
        
        $data = [
            'name' => $newName,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
        
        if ($shiftModel->update($id, $data)) {
            // If the shift name changed, update all workers with the old shift name
            if ($oldName !== $newName) {
                $db = \Config\Database::connect();
                $builder = $db->table('workers');
                
                // Update exact matches
                $builder->where('shift', $oldName);
                $builder->update(['shift' => $newName]);
                $exactMatches = $db->affectedRows();
                
                // Also update case-insensitive matches and trimmed versions
                $builder = $db->table('workers');
                $builder->where('LOWER(TRIM(shift))', strtolower(trim($oldName)));
                $builder->where('shift !=', $newName); // Don't update already updated ones
                $builder->update(['shift' => $newName]);
                $fuzzyMatches = $db->affectedRows();
                
                $totalUpdated = $exactMatches + $fuzzyMatches;
            }
            
            return redirect()->to(base_url('config/shifts'))->with('success', 'Shift updated successfully! ' . (isset($totalUpdated) ? "Updated $totalUpdated workers." : ''));
        } else {
            $errors = $shiftModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update shift.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleShift($id)
    {
        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);
        
        if ($shift) {
            $shiftModel->update($id, ['is_active' => !$shift['is_active']]);
            return redirect()->to(base_url('config/shifts'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/shifts'))->with('error', 'Shift not found.');
    }
    
    public function deleteShift($id)
    {
        $shiftModel = new ShiftModel();
        
        if ($shiftModel->delete($id)) {
            return redirect()->to(base_url('config/shifts'))->with('success', 'Shift deleted successfully!');
        }
        
        return redirect()->to(base_url('config/shifts'))->with('error', 'Failed to delete shift.');
    }
    
    // Staff Group Methods
    public function staffGroups()
    {
        $staffGroupModel = new StaffGroupModel();
        
        $data = [
            'title' => 'Staff Groups',
            'user' => $this->getLoggedInUser(),
            'groups' => $staffGroupModel->orderBy('code', 'ASC')->findAll()
        ];

        return view('config/staff_groups', $data);
    }
    
    public function storeStaffGroup()
    {
        $staffGroupModel = new StaffGroupModel();
        
        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'note' => $this->request->getPost('note'),
            'is_active' => 1,
        ];
        
        if ($staffGroupModel->insert($data)) {
            return redirect()->to(base_url('config/staff-groups'))->with('success', 'Staff group added successfully!');
        } else {
            $errors = $staffGroupModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add staff group.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateStaffGroup()
    {
        $staffGroupModel = new StaffGroupModel();
        
        $id = $this->request->getPost('id');
        
        // Set validation rules that exclude current ID for uniqueness check
        $staffGroupModel->setValidationRule('code', 'required|min_length[2]|max_length[50]|is_unique[staff_groups.code,id,' . $id . ']');
        
        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'note' => $this->request->getPost('note'),
        ];
        
        if ($staffGroupModel->update($id, $data)) {
            return redirect()->to(base_url('config/staff-groups'))->with('success', 'Staff group updated successfully!');
        } else {
            $errors = $staffGroupModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update staff group.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleStaffGroup($id)
    {
        $staffGroupModel = new StaffGroupModel();
        $group = $staffGroupModel->find($id);
        
        if ($group) {
            $staffGroupModel->update($id, ['is_active' => !$group['is_active']]);
            return redirect()->to(base_url('config/staff-groups'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/staff-groups'))->with('error', 'Staff group not found.');
    }
    
    public function deleteStaffGroup($id)
    {
        $staffGroupModel = new StaffGroupModel();
        
        if ($staffGroupModel->delete($id)) {
            return redirect()->to(base_url('config/staff-groups'))->with('success', 'Staff group deleted successfully!');
        }
        
        return redirect()->to(base_url('config/staff-groups'))->with('error', 'Failed to delete staff group.');
    }
    
    // Public Holiday Methods
    public function publicHolidays()
    {
        $publicHolidayModel = new PublicHolidayModel();
        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('m');
        
        $data = [
            'title' => 'Public Holidays',
            'user' => $this->getLoggedInUser(),
            'holidays' => $publicHolidayModel->where('is_active', 1)->orderBy('holiday_date', 'ASC')->findAll(),
            'year' => $year,
            'month' => $month
        ];

        return view('config/public_holidays', $data);
    }
    
    public function storePublicHoliday()
    {
        $publicHolidayModel = new PublicHolidayModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'holiday_date' => $this->request->getPost('holiday_date'),
            'type' => $this->request->getPost('type'),
            'is_active' => 1,
        ];
        
        if ($publicHolidayModel->insert($data)) {
            return redirect()->to(base_url('config/public-holidays'))->with('success', 'Public holiday added successfully!');
        } else {
            $errors = $publicHolidayModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add public holiday.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updatePublicHoliday()
    {
        $publicHolidayModel = new PublicHolidayModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'holiday_date' => $this->request->getPost('holiday_date'),
            'type' => $this->request->getPost('type'),
        ];
        
        if ($publicHolidayModel->update($id, $data)) {
            return redirect()->to(base_url('config/public-holidays'))->with('success', 'Public holiday updated successfully!');
        } else {
            $errors = $publicHolidayModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update public holiday.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function togglePublicHoliday($id)
    {
        $publicHolidayModel = new PublicHolidayModel();
        $holiday = $publicHolidayModel->find($id);
        
        if ($holiday) {
            $publicHolidayModel->update($id, ['is_active' => !$holiday['is_active']]);
            return redirect()->to(base_url('config/public-holidays'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/public-holidays'))->with('error', 'Public holiday not found.');
    }
    
    public function deletePublicHoliday($id)
    {
        $publicHolidayModel = new PublicHolidayModel();
        
        if ($publicHolidayModel->delete($id)) {
            return redirect()->to(base_url('config/public-holidays'))->with('success', 'Public holiday deleted successfully!');
        }
        
        return redirect()->to(base_url('config/public-holidays'))->with('error', 'Failed to delete public holiday.');
    }
    
    // Groups Shift Methods
    public function groupsShift()
    {
        $groupsShiftModel = new GroupsShiftModel();
        $staffGroupModel = new StaffGroupModel();
        
        $data = [
            'title' => 'Groups Shift',
            'user' => $this->getLoggedInUser(),
            'groupsShifts' => $groupsShiftModel->orderBy('group', 'ASC')->findAll(),
            'staffGroups' => $staffGroupModel->where('is_active', 1)->orderBy('code', 'ASC')->findAll()
        ];

        return view('config/groups_shift', $data);
    }
    
    public function storeGroupsShift()
    {
        $groupsShiftModel = new GroupsShiftModel();
        
        $data = [
            'group' => $this->request->getPost('group'),
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'status' => $this->request->getPost('status'),
            'is_default' => 'NO',
            'is_active' => 1,
        ];
        
        if ($groupsShiftModel->insert($data)) {
            return redirect()->to(base_url('config/groups-shift'))->with('success', 'Groups shift added successfully!');
        } else {
            $errors = $groupsShiftModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add groups shift.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateGroupsShift()
    {
        $groupsShiftModel = new GroupsShiftModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'group' => $this->request->getPost('group'),
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'color' => $this->request->getPost('color'),
            'status' => $this->request->getPost('status'),
            'is_default' => $this->request->getPost('is_default'),
        ];
        
        if ($groupsShiftModel->update($id, $data)) {
            return redirect()->to(base_url('config/groups-shift'))->with('success', 'Groups shift updated successfully!');
        } else {
            $errors = $groupsShiftModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update groups shift.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleGroupsShift($id)
    {
        $groupsShiftModel = new GroupsShiftModel();
        $groupsShift = $groupsShiftModel->find($id);
        
        if ($groupsShift) {
            $groupsShiftModel->update($id, ['is_active' => !$groupsShift['is_active']]);
            return redirect()->to(base_url('config/groups-shift'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/groups-shift'))->with('error', 'Groups shift not found.');
    }
    
    public function deleteGroupsShift($id)
    {
        $groupsShiftModel = new GroupsShiftModel();
        
        if ($groupsShiftModel->delete($id)) {
            return redirect()->to(base_url('config/groups-shift'))->with('success', 'Groups shift deleted successfully!');
        }
        
        return redirect()->to(base_url('config/groups-shift'))->with('error', 'Failed to delete groups shift.');
    }
    
    // Staff Availability Methods
    public function staffAvailability()
    {
        $staffAvailabilityModel = new StaffAvailabilityModel();
        
        $data = [
            'title' => 'Staff Availability',
            'user' => $this->getLoggedInUser(),
            'availabilities' => $staffAvailabilityModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('config/staff_availability', $data);
    }
    
    public function storeStaffAvailability()
    {
        $staffAvailabilityModel = new StaffAvailabilityModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
            'is_active' => 1,
        ];
        
        if ($staffAvailabilityModel->insert($data)) {
            return redirect()->to(base_url('config/staff-availability'))->with('success', 'Staff availability added successfully!');
        } else {
            $errors = $staffAvailabilityModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to add staff availability.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function updateStaffAvailability()
    {
        $staffAvailabilityModel = new StaffAvailabilityModel();
        
        $id = $this->request->getPost('id');
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
        ];
        
        if ($staffAvailabilityModel->update($id, $data)) {
            return redirect()->to(base_url('config/staff-availability'))->with('success', 'Staff availability updated successfully!');
        } else {
            $errors = $staffAvailabilityModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update staff availability.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }
    
    public function toggleStaffAvailability($id)
    {
        $staffAvailabilityModel = new StaffAvailabilityModel();
        $availability = $staffAvailabilityModel->find($id);
        
        if ($availability) {
            $staffAvailabilityModel->update($id, ['is_active' => !$availability['is_active']]);
            return redirect()->to(base_url('config/staff-availability'))->with('success', 'Status updated successfully!');
        }
        
        return redirect()->to(base_url('config/staff-availability'))->with('error', 'Staff availability not found.');
    }
    
    public function deleteStaffAvailability($id)
    {
        $staffAvailabilityModel = new StaffAvailabilityModel();
        
        if ($staffAvailabilityModel->delete($id)) {
            return redirect()->to(base_url('config/staff-availability'))->with('success', 'Staff availability deleted successfully!');
        }
        
        return redirect()->to(base_url('config/staff-availability'))->with('error', 'Failed to delete staff availability.');
    }
    
    // Staff Shift Allocation Methods
    public function staffShiftAllocation()
    {
        $staffShiftAllocationModel = new StaffShiftAllocationModel();
        $groupsShiftModel = new GroupsShiftModel();
        
        // Get distinct group allocations with date ranges
        $query = "SELECT group_id, MIN(allocation_date) as start_date, MAX(allocation_date) as end_date, 
                  COUNT(DISTINCT allocation_date) as total_days, is_active
                  FROM staff_shift_allocation 
                  GROUP BY group_id, is_active
                  ORDER BY group_id, start_date DESC";
        
        $allocations = $staffShiftAllocationModel->query($query)->getResultArray();
        
        $data = [
            'title' => 'Staff Shift Allocation Config',
            'user' => $this->getLoggedInUser(),
            'allocations' => $allocations,
            'groupsShifts' => $groupsShiftModel->where('is_active', 1)->orderBy('group', 'ASC')->findAll()
        ];

        return view('config/staff_shift_allocation', $data);
    }
    
    public function addStaffShiftAllocation()
    {
        $groupsShiftModel = new GroupsShiftModel();
        $staffShiftAllocationModel = new StaffShiftAllocationModel();
        
        $groupId = $this->request->getGet('groupId');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');
        
        $allocations = [];
        $dateRange = [];
        
        if ($groupId && $startDate && $endDate) {
            // Check if allocations already exist
            $existingAllocations = $staffShiftAllocationModel->getAllocationsByGroupAndDateRange($groupId, $startDate, $endDate);
            
            // Create associative array for easy lookup
            foreach ($existingAllocations as $allocation) {
                $allocations[$allocation['allocation_date']] = $allocation['shift_code'];
            }
            
            // Generate date range
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $end->modify('+1 day');
            
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);
            
            foreach ($period as $date) {
                $dateRange[] = $date->format('Y-m-d');
            }
        }
        
        $data = [
            'title' => 'Add Staff Shift Allocation',
            'user' => $this->getLoggedInUser(),
            'groupsShifts' => $groupsShiftModel->where('is_active', 1)->orderBy('group', 'ASC')->findAll(),
            'shiftCodes' => $groupsShiftModel->select('code')->where('is_active', 1)->distinct()->findAll(),
            'selectedGroupId' => $groupId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRange' => $dateRange,
            'allocations' => $allocations,
            'mode' => 'add'
        ];

        return view('config/staff_shift_allocation_form', $data);
    }
    
    public function editStaffShiftAllocation()
    {
        $staffShiftAllocationModel = new StaffShiftAllocationModel();
        $groupsShiftModel = new GroupsShiftModel();
        
        $groupId = $this->request->getGet('groupId');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');
        
        if (!$groupId || !$startDate || !$endDate) {
            return redirect()->to(base_url('config/staff-shift-allocation'))->with('error', 'Invalid parameters');
        }
        
        // Get existing allocations
        $existingAllocations = $staffShiftAllocationModel->getAllocationsByGroupAndDateRange($groupId, $startDate, $endDate);
        
        // Create associative array for easy lookup
        $allocations = [];
        foreach ($existingAllocations as $allocation) {
            $allocations[$allocation['allocation_date']] = $allocation['shift_code'];
        }
        
        // Generate date range
        $dateRange = [];
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->modify('+1 day');
        
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);
        
        foreach ($period as $date) {
            $dateRange[] = $date->format('Y-m-d');
        }
        
        $data = [
            'title' => 'Edit Staff Shift Allocation',
            'user' => $this->getLoggedInUser(),
            'groupsShifts' => $groupsShiftModel->where('is_active', 1)->orderBy('group', 'ASC')->findAll(),
            'shiftCodes' => $groupsShiftModel->select('code')->where('is_active', 1)->distinct()->findAll(),
            'selectedGroupId' => $groupId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRange' => $dateRange,
            'allocations' => $allocations,
            'mode' => 'edit'
        ];

        return view('config/staff_shift_allocation_form', $data);
    }
    
    public function saveStaffShiftAllocation()
    {
        $staffShiftAllocationModel = new StaffShiftAllocationModel();
        
        $groupId = $this->request->getPost('group_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $allocations = $this->request->getPost('allocations');
        
        if (!$groupId || !$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Please select group and date range.');
        }
        
        // Delete existing allocations for this group and date range
        $staffShiftAllocationModel->deleteByGroupAndDateRange($groupId, $startDate, $endDate);
        
        // Insert new allocations
        if (!empty($allocations)) {
            foreach ($allocations as $date => $shiftCode) {
                if (!empty($shiftCode)) {
                    $staffShiftAllocationModel->insert([
                        'group_id' => $groupId,
                        'allocation_date' => $date,
                        'shift_code' => $shiftCode,
                        'is_active' => 1
                    ]);
                }
            }
        }
        
        return redirect()->to(base_url('config/staff-shift-allocation?groupId=' . $groupId . '&startDate=' . $startDate . '&endDate=' . $endDate))
                        ->with('success', 'Shift allocation saved successfully!');
    }
    
    public function copyStaffShiftSequence()
    {
        $staffShiftAllocationModel = new StaffShiftAllocationModel();
        
        $groupId = $this->request->getPost('group_id');
        $sourceStartDate = $this->request->getPost('source_start_date');
        $sourceEndDate = $this->request->getPost('source_end_date');
        $targetStartDate = $this->request->getPost('target_start_date');
        
        if (!$groupId || !$sourceStartDate || !$sourceEndDate || !$targetStartDate) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Get source allocations
        $sourceAllocations = $staffShiftAllocationModel->getAllocationsByGroupAndDateRange($groupId, $sourceStartDate, $sourceEndDate);
        
        if (empty($sourceAllocations)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No source data to copy']);
        }
        
        // Calculate day difference
        $sourceStart = new \DateTime($sourceStartDate);
        $targetStart = new \DateTime($targetStartDate);
        $dayDiff = $sourceStart->diff($targetStart)->days;
        $isForward = $targetStart > $sourceStart;
        
        // Copy allocations
        foreach ($sourceAllocations as $allocation) {
            $sourceDate = new \DateTime($allocation['allocation_date']);
            $targetDate = clone $sourceDate;
            
            if ($isForward) {
                $targetDate->modify("+{$dayDiff} days");
            } else {
                $targetDate->modify("-{$dayDiff} days");
            }
            
            // Check if allocation already exists
            $existing = $staffShiftAllocationModel->where('group_id', $groupId)
                                                  ->where('allocation_date', $targetDate->format('Y-m-d'))
                                                  ->first();
            
            if ($existing) {
                $staffShiftAllocationModel->update($existing['id'], [
                    'shift_code' => $allocation['shift_code']
                ]);
            } else {
                $staffShiftAllocationModel->insert([
                    'group_id' => $groupId,
                    'allocation_date' => $targetDate->format('Y-m-d'),
                    'shift_code' => $allocation['shift_code'],
                    'is_active' => 1
                ]);
            }
        }
        
        return $this->response->setJSON(['success' => true, 'message' => 'Sequence copied successfully']);
    }
    
    public function deleteStaffShiftAllocation()
    {
        $staffShiftAllocationModel = new StaffShiftAllocationModel();
        
        $groupId = $this->request->getGet('groupId');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');
        
        if (!$groupId || !$startDate || !$endDate) {
            return redirect()->to(base_url('config/staff-shift-allocation'))->with('error', 'Invalid parameters');
        }
        
        if ($staffShiftAllocationModel->deleteByGroupAndDateRange($groupId, $startDate, $endDate)) {
            return redirect()->to(base_url('config/staff-shift-allocation'))->with('success', 'Shift allocation deleted successfully!');
        }
        
        return redirect()->to(base_url('config/staff-shift-allocation'))->with('error', 'Failed to delete shift allocation.');
    }
    
    // Leave Reason Methods
    public function leaveReasons()
    {
        $leaveReasonModel = new LeaveReasonModel();
        
        $data = [
            'title' => 'Leave Reason List',
            'user' => $this->getLoggedInUser(),
            'reasons' => $leaveReasonModel->orderBy('type', 'ASC')->orderBy('name', 'ASC')->findAll()
        ];

        return view('config/leave_reasons', $data);
    }
    
    public function storeLeaveReason()
    {
        $leaveReasonModel = new LeaveReasonModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1
        ];
        
        if ($leaveReasonModel->insert($data)) {
            return redirect()->to(base_url('config/leave-reasons'))->with('success', 'Leave reason added successfully!');
        } else {
            $errors = $leaveReasonModel->errors();
            return redirect()->back()->withInput()->with('error', 'Failed to add leave reason: ' . implode(', ', $errors));
        }
    }
    
    public function updateLeaveReason()
    {
        $leaveReasonModel = new LeaveReasonModel();
        
        $id = $this->request->getPost('id');
        $data = [
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'description' => $this->request->getPost('description')
        ];
        
        if ($leaveReasonModel->update($id, $data)) {
            return redirect()->to(base_url('config/leave-reasons'))->with('success', 'Leave reason updated successfully!');
        } else {
            $errors = $leaveReasonModel->errors();
            return redirect()->back()->withInput()->with('error', 'Failed to update leave reason: ' . implode(', ', $errors));
        }
    }
    
    public function toggleLeaveReason($id)
    {
        $leaveReasonModel = new LeaveReasonModel();
        $reason = $leaveReasonModel->find($id);
        
        if ($reason) {
            $leaveReasonModel->update($id, ['is_active' => !$reason['is_active']]);
            return redirect()->to(base_url('config/leave-reasons'))->with('success', 'Leave reason status updated!');
        }
        
        return redirect()->to(base_url('config/leave-reasons'))->with('error', 'Leave reason not found.');
    }
    
    public function deleteLeaveReason($id)
    {
        $leaveReasonModel = new LeaveReasonModel();
        
        if ($leaveReasonModel->delete($id)) {
            return redirect()->to(base_url('config/leave-reasons'))->with('success', 'Leave reason deleted successfully!');
        }
        
        return redirect()->to(base_url('config/leave-reasons'))->with('error', 'Failed to delete leave reason.');
    }
    
    public function systemLogs()
    {
        $logPath = WRITEPATH . 'logs/';
        $level = $this->request->getGet('level') ?? 'all';
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        
        // Get available log files
        $logFiles = [];
        if (is_dir($logPath)) {
            $files = scandir($logPath);
            foreach ($files as $file) {
                if (preg_match('/^log-(\d{4}-\d{2}-\d{2})\.log$/', $file, $matches)) {
                    $logFiles[] = $matches[1];
                }
            }
            rsort($logFiles); // Sort descending (newest first)
        }
        
        // Get all log entries for statistics
        $allLogEntries = $this->getLogEntries($logPath, $date, $level);
        
        // Get first 10 entries for initial load
        $logEntries = array_slice($allLogEntries, 0, 10);
        
        // Calculate statistics from all entries (not filtered by level for accurate counts)
        $allLogsForStats = $this->getLogEntries($logPath, $date, 'all');
        $stats = [
            'total' => count($allLogsForStats),
            'critical' => count(array_filter($allLogsForStats, fn($e) => $e['level'] === 'CRITICAL')),
            'error' => count(array_filter($allLogsForStats, fn($e) => $e['level'] === 'ERROR')),
            'warning' => count(array_filter($allLogsForStats, fn($e) => $e['level'] === 'WARNING')),
            'info' => count(array_filter($allLogsForStats, fn($e) => $e['level'] === 'INFO')),
            'debug' => count(array_filter($allLogsForStats, fn($e) => $e['level'] === 'DEBUG'))
        ];
        
        $data = [
            'title' => 'System Logs',
            'user' => $this->getLoggedInUser(),
            'logFiles' => $logFiles,
            'logEntries' => $logEntries,
            'totalEntries' => count($allLogEntries),
            'stats' => $stats,
            'selectedLevel' => $level,
            'selectedDate' => $date
        ];

        return view('config/system_logs', $data);
    }
    
    public function loadMoreLogs()
    {
        $logPath = WRITEPATH . 'logs/';
        $level = $this->request->getGet('level') ?? 'all';
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $offset = (int)($this->request->getGet('offset') ?? 0);
        $limit = 10;
        
        $allLogEntries = $this->getLogEntries($logPath, $date, $level);
        $logEntries = array_slice($allLogEntries, $offset, $limit);
        
        return $this->response->setJSON([
            'logs' => $logEntries,
            'hasMore' => ($offset + $limit) < count($allLogEntries)
        ]);
    }
    
    private function getLogEntries($logPath, $date, $level)
    {
        $logEntries = [];
        $logFile = $logPath . 'log-' . $date . '.log';
        
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                // Parse log entry
                if (preg_match('/^(CRITICAL|ERROR|WARNING|INFO|DEBUG|NOTICE|ALERT|EMERGENCY)\s+-\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+-->\s+(.+)$/', $line, $matches)) {
                    $entryLevel = $matches[1];
                    $timestamp = $matches[2];
                    $message = $matches[3];
                    
                    // Filter by level
                    if ($level === 'all' || strtolower($entryLevel) === strtolower($level)) {
                        $logEntries[] = [
                            'level' => $entryLevel,
                            'timestamp' => $timestamp,
                            'message' => $message,
                            'raw' => $line
                        ];
                    }
                }
            }
        }
        
        return $logEntries;
    }
    
    private function convertTo24Hour($hour, $period)
    {
        $hour = intval($hour);
        
        if ($period === 'PM' && $hour !== 12) {
            $hour += 12;
        } elseif ($period === 'AM' && $hour === 12) {
            $hour = 0;
        }
        
        return str_pad($hour, 2, '0', STR_PAD_LEFT);
    }

    // Role Management
// Update the roles() method to fetch permissions
public function roles()
{
    $db = \Config\Database::connect();
    $rolesBuilder = $db->table('roles');
    $roles = $rolesBuilder->orderBy('role_name', 'ASC')->get()->getResultArray();
    
    // Fetch permissions for each role
    $permissionsBuilder = $db->table('role_permissions');
    foreach ($roles as &$role) {
        $permissions = $permissionsBuilder
            ->select('permission')
            ->where('role_id', $role['id'])
            ->get()
            ->getResultArray();
        
        $role['permissions'] = array_column($permissions, 'permission');
    }
    
    $data = [
        'title' => 'Role List',
        'user' => $this->getLoggedInUser(),
        'roles' => $roles
    ];

    return view('config/roles', $data);
}

// Update storeRole to handle permissions
public function storeRole()
{
    $db = \Config\Database::connect();
    $rolesBuilder = $db->table('roles');
    
    $data = [
        'role_name' => $this->request->getPost('role_name'),
        'description' => $this->request->getPost('description'),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    
    if ($rolesBuilder->insert($data)) {
        $roleId = $db->insertID();
        
        // Insert permissions
        $permissions = $this->request->getPost('permissions');
        if ($permissions && is_array($permissions)) {
            $permissionsBuilder = $db->table('role_permissions');
            foreach ($permissions as $permission) {
                $permissionsBuilder->insert([
                    'role_id' => $roleId,
                    'permission' => $permission,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        return redirect()->to(base_url('config/roles'))->with('success', 'Role added successfully!');
    } else {
        return redirect()->back()->with('error', 'Failed to add role.');
    }
}

// Update updateRole to handle permissions
public function updateRole()
{
    $db = \Config\Database::connect();
    $rolesBuilder = $db->table('roles');
    
    $id = $this->request->getPost('id');
    
    $data = [
        'role_name' => $this->request->getPost('role_name'),
        'description' => $this->request->getPost('description'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    
    if ($rolesBuilder->where('id', $id)->update($data)) {
        // Delete existing permissions
        $permissionsBuilder = $db->table('role_permissions');
        $permissionsBuilder->where('role_id', $id)->delete();
        
        // Insert new permissions
        $permissions = $this->request->getPost('permissions');
        if ($permissions && is_array($permissions)) {
            foreach ($permissions as $permission) {
                $permissionsBuilder->insert([
                    'role_id' => $id,
                    'permission' => $permission,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        return redirect()->to(base_url('config/roles'))->with('success', 'Role updated successfully!');
    } else {
        return redirect()->back()->with('error', 'Failed to update role.');
    }
  }

    public function unitsOfMeasure()
    {
        $model = new UnitOfMeasureModel();

        return view('config/units_of_measure', [
            'title' => 'Units of Measure',
            'user'  => $this->getLoggedInUser(),
            'units' => $model->orderBy('sort_order', 'ASC')->orderBy('label', 'ASC')->findAll(),
        ]);
    }

    public function storeUnitOfMeasure()
    {
        $model = new UnitOfMeasureModel();

        $data = [
            'code'        => strtolower(trim((string) $this->request->getPost('code'))),
            'label'       => trim((string) $this->request->getPost('label')),
            'description' => $this->request->getPost('description') ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'   => 1,
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('config/units-of-measure'))->with('success', 'Unit of measure added successfully.');
        }

        return redirect()->back()->withInput()->with('error', implode(', ', $model->errors()));
    }

    public function updateUnitOfMeasure()
    {
        $model = new UnitOfMeasureModel();
        $id    = (int) $this->request->getPost('id');

        $data = [
            'id'          => $id,
            'code'        => strtolower(trim((string) $this->request->getPost('code'))),
            'label'       => trim((string) $this->request->getPost('label')),
            'description' => $this->request->getPost('description') ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
        ];

        if ($model->update($id, $data)) {
            return redirect()->to(base_url('config/units-of-measure'))->with('success', 'Unit of measure updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', implode(', ', $model->errors()));
    }

    public function toggleUnitOfMeasure($id)
    {
        $model = new UnitOfMeasureModel();
        $unit  = $model->find($id);

        if (!$unit) {
            return redirect()->to(base_url('config/units-of-measure'))->with('error', 'Unit not found.');
        }

        $model->update($id, ['is_active' => $unit['is_active'] ? 0 : 1]);

        return redirect()->to(base_url('config/units-of-measure'))->with('success', 'Unit status updated.');
    }

    public function deleteUnitOfMeasure($id)
    {
        $model = new UnitOfMeasureModel();

        if ($model->delete($id)) {
            return redirect()->to(base_url('config/units-of-measure'))->with('success', 'Unit deleted.');
        }

        return redirect()->to(base_url('config/units-of-measure'))->with('error', 'Failed to delete unit.');
    }

    public function suppliers()
    {
        $model = new SupplierModel();

        return view('config/suppliers', [
            'title'     => 'Suppliers',
            'user'      => $this->getLoggedInUser(),
            'suppliers' => $model->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function storeSupplier()
    {
        $model = new SupplierModel();

        $data = [
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => $this->request->getPost('description') ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'   => 1,
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('config/suppliers'))->with('success', 'Supplier added successfully.');
        }

        return redirect()->back()->withInput()->with('error', implode(', ', $model->errors()));
    }

    public function updateSupplier()
    {
        $model = new SupplierModel();
        $id    = (int) $this->request->getPost('id');

        $data = [
            'id'          => $id,
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => $this->request->getPost('description') ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
        ];

        if ($model->update($id, $data)) {
            return redirect()->to(base_url('config/suppliers'))->with('success', 'Supplier updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', implode(', ', $model->errors()));
    }

    public function toggleSupplier($id)
    {
        $model    = new SupplierModel();
        $supplier = $model->find($id);

        if (!$supplier) {
            return redirect()->to(base_url('config/suppliers'))->with('error', 'Supplier not found.');
        }

        $model->update($id, ['is_active' => $supplier['is_active'] ? 0 : 1]);

        return redirect()->to(base_url('config/suppliers'))->with('success', 'Supplier status updated.');
    }

    public function deleteSupplier($id)
    {
        $model = new SupplierModel();

        if ($model->delete($id)) {
            return redirect()->to(base_url('config/suppliers'))->with('success', 'Supplier deleted.');
        }

        return redirect()->to(base_url('config/suppliers'))->with('error', 'Failed to delete supplier.');
    }
}