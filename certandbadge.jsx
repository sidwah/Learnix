import React from 'react';

const LearnixBadgeSystem = () => {
  return (
    <div className="flex flex-col gap-8 p-6 bg-gray-50 text-gray-800 font-sans">
      <header className="border-b border-gray-300 pb-4">
        <h1 className="text-3xl font-bold text-blue-800">Learnix Badge & Certificate System</h1>
        <p className="text-gray-600 mt-2">Visual organization for the badge and certificate template system</p>
      </header>
      
      {/* Badge Tiers Section */}
      <section className="mb-6">
        <h2 className="text-2xl font-bold mb-4 text-blue-700">Badge Tier Templates</h2>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          {/* Bronze Tier */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-xl font-semibold mb-3 text-amber-700 flex items-center">
              <div className="w-8 h-8 rounded-full bg-amber-800 bg-opacity-40 flex items-center justify-center mr-2">
                <span className="text-amber-900">B</span>
              </div>
              Bronze Tier
            </h3>
            <div className="flex flex-col items-center p-3 bg-amber-50 rounded-lg mb-2">
              <div className="w-28 h-28 mb-3 rounded-full border-4 border-amber-700 flex items-center justify-center bg-amber-100">
                <div className="w-16 h-16 text-center flex items-center justify-center text-amber-800">
                  <span className="text-xs">Subject Icon</span>
                </div>
              </div>
              <div className="bg-amber-700 text-white text-sm py-1 px-3 rounded-full">Section Completion</div>
            </div>
            <p className="text-xs text-gray-500 mt-2">Used for basic accomplishments and section completions</p>
          </div>
          
          {/* Silver Tier */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-xl font-semibold mb-3 text-gray-600 flex items-center">
              <div className="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center mr-2">
                <span className="text-gray-700">S</span>
              </div>
              Silver Tier
            </h3>
            <div className="flex flex-col items-center p-3 bg-gray-50 rounded-lg mb-2">
              <div className="w-28 h-28 mb-3 rounded-lg border-4 border-gray-400 flex items-center justify-center bg-gray-100">
                <div className="w-16 h-16 text-center flex items-center justify-center text-gray-600">
                  <span className="text-xs">Subject Icon</span>
                </div>
              </div>
              <div className="bg-gray-500 text-white text-sm py-1 px-3 rounded-full">Quiz Excellence</div>
            </div>
            <p className="text-xs text-gray-500 mt-2">Used for intermediate achievements and quiz excellence</p>
          </div>
          
          {/* Gold Tier */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-xl font-semibold mb-3 text-yellow-600 flex items-center">
              <div className="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center mr-2">
                <span className="text-yellow-800">G</span>
              </div>
              Gold Tier
            </h3>
            <div className="flex flex-col items-center p-3 bg-yellow-50 rounded-lg mb-2">
              <div className="w-28 h-28 mb-3 rounded-lg border-4 border-yellow-500 flex items-center justify-center bg-yellow-100" style={{clipPath: 'polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%)'}}>
                <div className="w-16 h-16 text-center flex items-center justify-center text-yellow-700">
                  <span className="text-xs">Subject Icon</span>
                </div>
              </div>
              <div className="bg-yellow-500 text-white text-sm py-1 px-3 rounded-full">Course Completion</div>
            </div>
            <p className="text-xs text-gray-500 mt-2">Used for course completions and high achievements</p>
          </div>
          
          {/* Platinum Tier */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-xl font-semibold mb-3 text-blue-600 flex items-center">
              <div className="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center mr-2">
                <span className="text-blue-800">P</span>
              </div>
              Platinum Tier
            </h3>
            <div className="flex flex-col items-center p-3 bg-blue-50 rounded-lg mb-2">
              <div className="w-28 h-28 mb-3 border-4 border-blue-400 flex items-center justify-center bg-blue-100" style={{clipPath: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'}}>
                <div className="w-16 h-16 text-center flex items-center justify-center text-blue-700">
                  <span className="text-xs">Subject Icon</span>
                </div>
              </div>
              <div className="bg-blue-500 text-white text-sm py-1 px-3 rounded-full">Special Achievement</div>
            </div>
            <p className="text-xs text-gray-500 mt-2">Used for exceptional achievements and special recognitions</p>
          </div>
        </div>
      </section>
      
      {/* Badge Components Section */}
      <section className="mb-6">
        <h2 className="text-2xl font-bold mb-4 text-blue-700">Badge Component Structure</h2>
        <div className="flex flex-col md:flex-row gap-6">
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200 md:w-3/5">
            <h3 className="text-xl font-semibold mb-3">Component Layers</h3>
            <div className="flex items-center justify-center mb-6">
              <div className="relative w-64 h-64">
                {/* Base Frame */}
                <div className="absolute inset-0 border-8 border-yellow-500 rounded-full flex items-center justify-center bg-yellow-100 z-0">
                  <span className="text-gray-400 text-xs">4. Base Frame (Tier)</span>
                </div>
                
                {/* Subject Icon */}
                <div className="absolute inset-8 bg-white rounded-full flex items-center justify-center z-10">
                  <div className="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center">
                    <span className="text-gray-600 text-xs">3. Subject Icon</span>
                  </div>
                </div>
                
                {/* Achievement Type */}
                <div className="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-green-600 text-white py-1 px-4 rounded-full text-xs z-20">
                  2. Achievement Type
                </div>
                
                {/* Text Label */}
                <div className="absolute -top-2 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white py-1 px-4 rounded-full text-xs z-20">
                  1. Badge Text
                </div>
              </div>
            </div>
            <div className="text-sm text-gray-700">
              <p className="mb-2"><strong>Badge Structure:</strong> Badges are built in layers with each component having a specific purpose.</p>
              <ol className="list-decimal pl-5 space-y-1">
                <li>Text Label: Displays the specific achievement name</li>
                <li>Achievement Type: Visual indicator for completion type</li>
                <li>Subject Icon: Represents the course domain</li>
                <li>Base Frame: Indicates the tier level (bronze, silver, gold, platinum)</li>
              </ol>
            </div>
          </div>
          
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200 md:w-2/5">
            <h3 className="text-xl font-semibold mb-3">Badge Type Indicators</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="flex flex-col items-center p-3 bg-green-50 rounded-lg">
                <div className="w-16 h-16 bg-green-100 rounded-full border-2 border-green-400 flex items-center justify-center mb-2">
                  <div className="w-8 h-8 bg-green-600 rounded-sm"></div>
                </div>
                <span className="text-green-600 text-xs font-medium">Section Completion</span>
              </div>
              
              <div className="flex flex-col items-center p-3 bg-yellow-50 rounded-lg">
                <div className="w-16 h-16 bg-yellow-100 rounded-full border-2 border-yellow-400 flex items-center justify-center mb-2">
                  <div className="w-8 h-8 bg-yellow-600 rounded-md" style={{clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)'}}></div>
                </div>
                <span className="text-yellow-600 text-xs font-medium">Course Completion</span>
              </div>
              
              <div className="flex flex-col items-center p-3 bg-purple-50 rounded-lg">
                <div className="w-16 h-16 bg-purple-100 rounded-full border-2 border-purple-400 flex items-center justify-center mb-2">
                  <div className="w-8 h-8 bg-purple-600" style={{clipPath: 'polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)'}}></div>
                </div>
                <span className="text-purple-600 text-xs font-medium">Quiz Excellence</span>
              </div>
              
              <div className="flex flex-col items-center p-3 bg-blue-50 rounded-lg">
                <div className="w-16 h-16 bg-blue-100 rounded-full border-2 border-blue-400 flex items-center justify-center mb-2">
                  <div className="w-8 h-8 bg-blue-600" style={{clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'}}></div>
                </div>
                <span className="text-blue-600 text-xs font-medium">Special Achievement</span>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      {/* Achievement Badge Examples */}
      <section className="mb-6">
        <h2 className="text-2xl font-bold mb-4 text-blue-700">Example Badges by Achievement Type</h2>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {/* Section Badge */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-lg font-semibold mb-3 text-green-700">Section Completion</h3>
            <div className="flex flex-col items-center">
              <div className="relative w-40 h-40">
                <div className="absolute inset-0 border-4 border-amber-600 rounded-full flex items-center justify-center bg-amber-100">
                  <div className="w-24 h-24 rounded-full bg-white flex items-center justify-center">
                    <svg className="w-14 h-14 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838l-2.727 1.17 1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                  </div>
                </div>
                <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 bg-green-600 text-white py-1 px-3 rounded-full text-xs">
                  Module 3 Complete
                </div>
              </div>
              <div className="mt-6 text-center">
                <p className="text-xs text-gray-500">JavaScript Fundamentals</p>
                <p className="text-xs text-gray-400 mt-1">Earned: April 10, 2025</p>
              </div>
            </div>
          </div>
          
          {/* Course Badge */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-lg font-semibold mb-3 text-yellow-700">Course Completion</h3>
            <div className="flex flex-col items-center">
              <div className="relative w-40 h-40">
                <div className="absolute inset-0 border-4 border-yellow-500 rounded-lg flex items-center justify-center bg-yellow-50" style={{clipPath: 'polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%)'}}>
                  <div className="w-24 h-24 bg-white rounded-lg flex items-center justify-center" style={{clipPath: 'polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%)'}}>
                    <svg className="w-14 h-14 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                  </div>
                </div>
                <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 bg-yellow-600 text-white py-1 px-3 rounded-full text-xs">
                  Web Development
                </div>
              </div>
              <div className="mt-6 text-center">
                <p className="text-xs text-gray-500">Full Stack Development</p>
                <p className="text-xs text-gray-400 mt-1">Earned: April 5, 2025</p>
              </div>
            </div>
          </div>
          
          {/* Quiz Badge */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-lg font-semibold mb-3 text-purple-700">Quiz Excellence</h3>
            <div className="flex flex-col items-center">
              <div className="relative w-40 h-40">
                <div className="absolute inset-0 border-4 border-gray-400 rounded-full flex items-center justify-center bg-gray-50">
                  <div className="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                    <svg className="w-14 h-14 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                  </div>
                </div>
                <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 bg-purple-600 text-white py-1 px-3 rounded-full text-xs">
                  Perfect Score!
                </div>
              </div>
              <div className="mt-6 text-center">
                <p className="text-xs text-gray-500">Data Structures Quiz</p>
                <p className="text-xs text-gray-400 mt-1">Earned: March 28, 2025</p>
              </div>
            </div>
          </div>
          
          {/* Special Badge */}
          <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
            <h3 className="text-lg font-semibold mb-3 text-blue-700">Special Achievement</h3>
            <div className="flex flex-col items-center">
              <div className="relative w-40 h-40">
                <div className="absolute inset-0 border-4 border-blue-500 flex items-center justify-center bg-blue-50" style={{clipPath: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'}}>
                  <div className="w-24 h-24 bg-white flex items-center justify-center" style={{clipPath: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'}}>
                    <svg className="w-14 h-14 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z"/>
                    </svg>
                  </div>
                </div>
                <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white py-1 px-3 rounded-full text-xs">
                  Top Performer
                </div>
              </div>
              <div className="mt-6 text-center">
                <p className="text-xs text-gray-500">Data Science Program</p>
                <p className="text-xs text-gray-400 mt-1">Earned: April 12, 2025</p>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      {/* Certificate System */}
      <section>
        <h2 className="text-2xl font-bold mb-4 text-blue-700">Certificate System</h2>
        <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
          <div className="flex flex-col md:flex-row gap-6">
            <div className="md:w-2/3">
              <h3 className="text-xl font-semibold mb-3">Certificate Template</h3>
              <div className="border-2 border-gray-300 rounded-lg p-5 bg-white">
                <div className="border-8 border-blue-100 rounded p-4">
                  <div className="border border-blue-300 rounded p-6 bg-blue-50">
                    <div className="flex justify-between mb-4">
                      <img src="/api/placeholder/80/40" alt="Logo" className="rounded" />
                      <div className="text-right">
                        <div className="text-xs text-gray-500">Certificate ID: LRNX-2025-47892</div>
                        <div className="text-xs text-gray-500">Issued: April 14, 2025</div>
                      </div>
                    </div>
                    
                    <div className="text-center mb-8">
                      <h2 className="text-2xl font-bold text-blue-800 mb-1">CERTIFICATE OF COMPLETION</h2>
                      <p className="text-sm text-gray-600">This certifies that</p>
                      <p className="text-xl font-semibold text-gray-800 my-3">John Doe</p>
                      <p className="text-sm text-gray-600">has successfully completed</p>
                      <p className="text-lg font-medium text-blue-700 mt-2">Full Stack Web Development</p>
                      <p className="text-sm text-gray-500 mt-1">with a grade of 92%</p>
                    </div>
                    
                    <div className="flex justify-between mt-12">
                      <div className="text-center">
                        <div className="w-32 border-b border-gray-400 pb-1 mb-1"></div>
                        <p className="text-xs text-gray-500">Instructor Signature</p>
                      </div>
                      <div className="text-center">
                        <div className="w-32 border-b border-gray-400 pb-1 mb-1"></div>
                        <p className="text-xs text-gray-500">Program Director</p>
                      </div>
                    </div>
                    
                    <div className="flex justify-center mt-6">
                      <div className="border border-gray-300 rounded p-2 bg-white">
                        <div className="w-24 h-24 bg-gray-200 flex items-center justify-center">
                          <span className="text-xs text-gray-500">QR Verification</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div className="md:w-1/3">
              <h3 className="text-xl font-semibold mb-3">Certificate Features</h3>
              <ul className="space-y-3">
                <li className="flex items-start">
                  <svg className="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  <div>
                    <p className="font-medium text-gray-700">Unique Verification</p>
                    <p className="text-xs text-gray-500">Each certificate has a unique ID and QR code for verification</p>
                  </div>
                </li>
                <li className="flex items-start">
                  <svg className="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  <div>
                    <p className="font-medium text-gray-700">Multiple Templates</p>
                    <p className="text-xs text-gray-500">Different designs for course types, difficulty levels, and specializations</p>
                  </div>
                </li>
                <li className="flex items-start">
                  <svg className="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  <div>
                    <p className="font-medium text-gray-700">PDF Generation</p>
                    <p className="text-xs text-gray-500">High-quality downloadable PDFs for printing</p>
                  </div>
                </li>
                <li className="flex items-start">
                  <svg className="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  <div>
                    <p className="font-medium text-gray-700">Automatic Issuance</p>
                    <p className="text-xs text-gray-500">Generated automatically when course completion criteria are met</p>
                  </div>
                </li>
                <li className="flex items-start">
                  <svg className="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  <div>
                    <p className="font-medium text-gray-700">Social Sharing</p>
                    <p className="text-xs text-gray-500">One-click sharing to LinkedIn and other platforms</p>
                  </div>
                </li>
              </ul>
              
              <h3 className="text-xl font-semibold mt-6 mb-3">Certificate Templates</h3>
              <div className="space-y-3">
                <div className="p-3 bg-blue-50 rounded-lg border border-blue-200">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-blue-100 rounded flex items-center justify-center mr-3">
                      <svg className="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838l-2.727 1.17 1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z" />
                        <path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zm9.3 7.176A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 className="font-medium text-blue-800">Academic Template</h4>
                      <p className="text-xs text-blue-600">For structured academic courses</p>
                    </div>
                  </div>
                </div>
                
                <div className="p-3 bg-green-50 rounded-lg border border-green-200">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-green-100 rounded flex items-center justify-center mr-3">
                      <svg className="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M6.672 1.911a1 1 0 10-1.932.518l.259.966a1 1 0 001.932-.518l-.26-.966zM2.429 4.74a1 1 0 10-.517 1.932l.966.259a1 1 0 00.517-1.932l-.966-.26zm8.814-.569a1 1 0 00-1.415-1.414l-.707.707a1 1 0 101.415 1.415l.707-.708zm-7.071 7.072l.707-.707A1 1 0 003.465 9.12l-.708.707a1 1 0 001.415 1.415zm3.2-5.171a1 1 0 00-1.3 1.3l4 10a1 1 0 001.823.075l1.38-2.759 3.018 3.02a1 1 0 001.414-1.415l-3.019-3.02 2.76-1.379a1 1 0 00-.076-1.822l-10-4z" clipRule="evenodd" />
                      </svg>
                    </div>
                    <div>
                      <h4 className="font-medium text-green-800">Professional Skills</h4>
                      <p className="text-xs text-green-600">For career-oriented certifications</p>
                    </div>
                  </div>
                </div>
                
                <div className="p-3 bg-purple-50 rounded-lg border border-purple-200">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-purple-100 rounded flex items-center justify-center mr-3">
                      <svg className="w-6 h-6 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
                      </svg>
                    </div>
                    <div>
                      <h4 className="font-medium text-purple-800">Creative Arts</h4>
                      <p className="text-xs text-purple-600">For design and creative courses</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      {/* Badge Generation Process */}
      <section className="mt-8">
        <h2 className="text-2xl font-bold mb-4 text-blue-700">Badge Generation Process</h2>
        <div className="bg-white rounded-lg shadow-md p-5 border border-gray-200">
          <div className="flex flex-col items-center">
            <div className="flex flex-col md:flex-row items-center justify-between w-full max-w-4xl">
              {/* Student Progress */}
              <div className="flex flex-col items-center p-4 mb-4 md:mb-0">
                <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-2">
                  <svg className="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                  </svg>
                </div>
                <h3 className="font-semibold text-blue-700">1. Student Achievement</h3>
                <p className="text-xs text-gray-500 text-center mt-1">Student completes a course, section, or earns special recognition</p>
              </div>
              
              <div className="hidden md:block">
                <svg className="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </div>
              
              {/* Progress Tracker */}
              <div className="flex flex-col items-center p-4 mb-4 md:mb-0">
                <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-2">
                  <svg className="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                </div>
                <h3 className="font-semibold text-green-700">2. System Detects Achievement</h3>
                <p className="text-xs text-gray-500 text-center mt-1">ProgressTracker identifies completion and triggers badge check</p>
              </div>
              
              <div className="hidden md:block">
                <svg className="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </div>
              
              {/* Badge Generation */}
              <div className="flex flex-col items-center p-4 mb-4 md:mb-0">
                <div className="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-2">
                  <svg className="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clipRule="evenodd" />
                  </svg>
                </div>
                <h3 className="font-semibold text-yellow-700">3. Badge Creation</h3>
                <p className="text-xs text-gray-500 text-center mt-1">System generates badge from template based on achievement type</p>
              </div>
              
              <div className="hidden md:block">
                <svg className="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </div>
              
              {/* Award & Notification */}
              <div className="flex flex-col items-center p-4">
                <div className="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mb-2">
                  <svg className="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                  </svg>
                </div>
                <h3 className="font-semibold text-purple-700">4. Award & Notification</h3>
                <p className="text-xs text-gray-500 text-center mt-1">Badge awarded to user with notification and display animation</p>
              </div>
            </div>
            
            <div className="mt-12 w-full max-w-4xl">
              <h3 className="text-lg font-semibold mb-3 text-gray-700">Technical Implementation</h3>
              <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <pre className="text-xs text-gray-700 overflow-auto p-2">
                  {`// Sample badge generation pseudocode
function generateBadge(userId, achievementType, courseId, sectionId = null) {
  // 1. Get badge tier and template based on performance
  const userPerformance = ProgressTracker.getPerformanceMetrics(userId, courseId, sectionId);
  const badgeTier = determineBadgeTier(userPerformance);
  
  // 2. Load base template for the badge tier
  const baseTemplate = loadSvgTemplate(badgeTier); // e.g., "gold.svg"
  
  // 3. Get subject icon for the course
  const courseInfo = CourseRepository.getCourse(courseId);
  const subjectIcon = loadSubjectIcon(courseInfo.subjectArea);
  
  // 4. Get achievement type indicator
  const typeIndicator = loadTypeIndicator(achievementType);
  
  // 5. Create badge text
  const badgeText = createBadgeText(achievementType, courseInfo, sectionId);
  
  // 6. Combine all elements into final SVG
  const finalBadge = combineTemplateElements(
    baseTemplate, 
    subjectIcon,
    typeIndicator,
    badgeText
  );
  
  // 7. Save badge and create user badge record
  const badgeUrl = saveBadgeImage(finalBadge, userId);
  UserBadgeRepository.createUserBadge(userId, badgeUrl, courseId, sectionId);
  
  // 8. Trigger notification
  NotificationManager.sendBadgeAwardNotification(userId, badgeUrl);
  
  return badgeUrl;
}`}
                </pre>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      <footer className="mt-10 pt-6 border-t border-gray-300 text-center text-gray-500 text-sm">
        <p>Learnix Badge & Certificate System • Implementation Plan 2025</p>
      </footer>
    </div>
  );
};

export default LearnixBadgeSystem;