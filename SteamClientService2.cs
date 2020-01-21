using System;
using System.IO;
using System.ServiceProcess;
using System.Text;
using System.Threading;

// ref: https://docs.microsoft.com/en-us/dotnet/framework/windows-services/walkthrough-creating-a-windows-service-application-in-the-component-designer

namespace ParentSteamService
{
    public partial class SteamClientService2 : ServiceBase
    {
        // note: change access permissions on the hosts file so this service can edit the file
        private const string FILE_HOSTS = @"C:\Windows\System32\drivers\etc\hosts";

        private const string HEADER_HOSTS = @"# Copyright (c) 1993-2009 Microsoft Corp.
#
# This is a sample HOSTS file used by Microsoft TCP/IP for Windows.
#
# This file contains the mappings of IP addresses to host names. Each
# entry should be kept on an individual line. The IP address should
# be placed in the first column followed by the corresponding host name.
# The IP address and the host name should be separated by at least one
# space.
#
# Additionally, comments (such as these) may be inserted on individual
# lines or following the machine name denoted by a '#' symbol.
#
# For example:
#
#      102.54.94.97     rhino.acme.com          # source server
#       38.25.63.10     x.acme.com              # x client host

# localhost name resolution is handled within DNS itself.
#	127.0.0.1       localhost
#	::1             localhost
";

        private Thread _mThread = null;
        private bool _mWaitForExit = true;

        public SteamClientService2()
        {
            InitializeComponent();
        }

        protected override void OnStart(string[] args)
        {
            _mWaitForExit = true;
            ThreadStart ts = new ThreadStart(ThreadWorker);
            _mThread = new Thread(ts);
            _mThread.Start();
        }

        protected override void OnStop()
        {
            _mWaitForExit = false;
        }

        private void ThreadWorker()
        {
            while (_mWaitForExit)
            {
                Thread.Sleep(3000);
                if (!_mWaitForExit)
                {
                    break;
                }
                try
                {
                    using (FileStream fs = File.Open(FILE_HOSTS, FileMode.OpenOrCreate, FileAccess.Write, FileShare.ReadWrite))
                    {
                        using (StreamWriter sw = new StreamWriter(fs))
                        {
                            string contents = HEADER_HOSTS;
                            sw.Write(contents);
                            sw.Flush();
                        }
                    }
                }
                catch
                {

                }
            }
        }
    }
}
