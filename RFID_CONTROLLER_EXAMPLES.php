<?php
/**
 * Example code snippets to add to your Workers controller
 * for RFID tag management in the worker UI
 * 
 * File: app/Controllers/Workers.php
 */

// Add this method to your Workers controller

/**
 * Assign RFID tag to worker
 * POST /workers/assign-rfid
 */
public function assignRfid()
{
    if (!$this->request->isAJAX()) {
        return redirect()->back();
    }
    
    $data = $this->request->getJSON(true);
    $workerId = $data['worker_id'] ?? null;
    $rfidTagId = $data['rfid_tag_id'] ?? null;
    
    if (!$workerId || !$rfidTagId) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Worker ID and RFID Tag ID are required'
        ]);
    }
    
    $workerModel = new \App\Models\WorkerModel();
    
    // Check if tag is already assigned to another worker
    if ($workerModel->isRfidTagRegistered($rfidTagId, $workerId)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'This RFID tag is already assigned to another worker'
        ]);
    }
    
    // Update worker with RFID tag
    $workerModel->update($workerId, ['rfid_tag_id' => $rfidTagId]);
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'RFID tag assigned successfully'
    ]);
}

/**
 * Remove RFID tag from worker
 * POST /workers/remove-rfid
 */
public function removeRfid()
{
    if (!$this->request->isAJAX()) {
        return redirect()->back();
    }
    
    $data = $this->request->getJSON(true);
    $workerId = $data['worker_id'] ?? null;
    
    if (!$workerId) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Worker ID is required'
        ]);
    }
    
    $workerModel = new \App\Models\WorkerModel();
    $workerModel->update($workerId, ['rfid_tag_id' => null]);
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'RFID tag removed successfully'
    ]);
}

/**
 * Get workers with RFID tags
 * GET /workers/with-rfid
 */
public function withRfid()
{
    $workerModel = new \App\Models\WorkerModel();
    $workers = $workerModel->getWorkersWithRfidTags();
    
    $data = [
        'title' => 'Workers with RFID Tags',
        'workers' => $workers,
        'total_workers' => count($workers)
    ];
    
    return view('workers/rfid_list', $data);
}

/**
 * Scan for available RFID tags
 * This would integrate with your RFID reader to detect nearby tags
 * GET /workers/scan-rfid
 */
public function scanRfid()
{
    $rfidReader = new \App\Libraries\YanzeoSA810();
    
    // This would depend on your reader's capabilities
    // Some readers support inventory scanning
    $status = $rfidReader->getStatus();
    
    return $this->response->setJSON($status);
}
