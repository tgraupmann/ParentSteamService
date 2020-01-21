using System.ServiceProcess;

namespace ParentSteamService
{
    static class Program
    {
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        static void Main()
        {
            ServiceBase[] ServicesToRun;
            ServicesToRun = new ServiceBase[]
            {
                new SteamClientService2()
            };
            ServiceBase.Run(ServicesToRun);
        }
    }
}
